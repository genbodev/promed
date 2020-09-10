/* 
Выбор первичного вызова
*/


Ext.define('sw.tools.swSelectFirstSmpCallCard', {
	alias: 'widget.swSelectFirstSmpCallCard',
	extend: 'sw.standartToolsWindow',
	title: 'Выбор первичного вызова',
    refId: 'swSelectFirstSmpCallCard',
	width: 1300,
	height: 400,
    closeAction: 'destroy',
    autoDestroy: true,

	toggleSearchOptionsPage: function(){
		this.selectOptions.setVisible(true);
		this.topTbar.setVisible(false);
		this.swSelectFirstSmpCallCard.setVisible(false);
		this.down('button[refId=okButton]').setVisible(false);
		this.selectOptions.focus();
	},

	filterResults: function(c){
		var win = this,
			form = win.down('form').getForm(),
			grid = win.swSelectFirstSmpCallCard,
			nameField = form.findField('filterByName'),
			famField = form.findField('filterByFamily'),
			secNameField = form.findField('filterBySecName'),
			addressData = form.findField('filterByAddress').addressParams;

        grid.store.filterBy(function(rec){

			var filtered = false;
			//если не указаны диапазоны карт-идишников и пусто в городе или пусто в Ф И, тогда сразу прекращаем проверку
			if(
				(!win.filterCmpCalls || win.filterCmpCalls.length == 0) &&
				!(
					( (addressData.KLCity_id || addressData.KLTown_id) && addressData.KLStreet_id ) ||
					(nameField.getValue() && famField.getValue())
				)
			){
				return false;
			}

			//вернул, требуется для исключения вызова из списка дублирующих вызовов в Арме ДП
			if (win.filterCmpCalls && rec.get('CmpCallCard_id').inlist(win.filterCmpCalls) && filtered == false) {
				filtered = true;
			}
			if(win.CmpCallCard_id && (rec.get('CmpCallCard_id') == win.CmpCallCard_id))
				filtered = false;

			if(
				(
					((!addressData.KLCity_id || (rec.get('KLCity_id') == addressData.KLCity_id)) &&
					(!addressData.KLTown_id || (rec.get('KLTown_id') == addressData.KLTown_id)) &&
					(!addressData.KLStreet_id || (rec.get('KLStreet_id') == addressData.KLStreet_id)) &&

					(!addressData.House || (rec.get('CmpCallCard_Dom') == addressData.House)) &&
					(!addressData.Corpus || (rec.get('CmpCallCard_Korp') == addressData.Corpus)) &&
					(!addressData.Flat || (rec.get('CmpCallCard_Kvar') == addressData.Flat))) &&

					(!famField.getValue() || (rec.get('Person_Surname') == famField.getValue())) &&
					(!nameField.getValue() || (rec.get('Person_Firname') == nameField.getValue())) &&
					(!secNameField.getValue() || (rec.get('Person_Secname') == secNameField.getValue())) &&
					(rec.get('CmpCallCard_id') != win.CmpCallCard_id)
				)
			){
				filtered = true;
			} else if (!win.CmpCallCard_id) filtered = false;

			return filtered;
		});
        if (c) focus(c);
	},

	enabledAll: function(){
		this.down('button[refId=informCall]').setDisabled( false );
		this.down('button[refId=informCall]').focus();
		this.down('button[refId=doublenewCall]').setDisabled( false );
		this.down('button[refId=feelBadlyCall]').setDisabled( false );
		this.down('button[refId=rejectCall]').setDisabled( false );
		this.down('button[refId=doubleCall]').setDisabled( false );
	},

	resetFilter: function(){
		var win = this,
			form = win.down('form').getForm(),
			grid = win.swSelectFirstSmpCallCard,
			nameField = form.findField('filterByName'),
			famField = form.findField('filterByFamily'),
			secNameField = form.findField('filterBySecName'),
			addressField = form.findField('filterByAddress');
		nameField.reset();
		famField.reset();
		secNameField.reset();
		addressField.reset();

        addressField.addressParams =  {
            Address_Zip: 0,
            Address_begDate: 0,
            Corpus: 0,
            Country_id: ( getRegionNick() == 'kz' ) ? 398 : 643,
            Flat: 0,
            House: 0,
            KLCity_id: 0,
            KLRegion_id: getGlobalOptions().region.number,
            KLStreet_id: 0,
            KLSubRGN_id: 0,
            KLTown_id: 0
        };

		win.filterResults();
		grid.getStore().sort();
	},

	initComponent: function() {
		var win = this,
			conf = win.initialConfig,
			allFields = (conf.params.checkBy != 'DP_doDouble')?Ext.ComponentQuery.query('[refId=dispatchCallForm]')[0].getForm().getAllFields(): null;

		win.addEvents({
			doubleCall: true,
			boostCall: true,
			feelBadlyCall: true
		});

        win.on('show', function(conf){

            //if(!(conf.params && conf.params.checkBy)) return;
			win.CallUnderControl = false;
			var form = win.down('form').getForm(),
                nameField = form.findField('filterByName'),
                famField = form.findField('filterByFamily'),
                secNameField = form.findField('filterBySecName'),
                addressField = form.findField('filterByAddress'),
				addrText = '',
				dCityCombo = allFields?allFields.dCityCombo.getSelectedRecord(): null;

			if (conf.params.checkBy == 'CallUnderControl') {
				this.toggleSearchOptionsPage();
				win.down('button[refId=thirdPersonCall]').setVisible(true);
				if (conf.params.callIsOverdue) {
					notificationManager.showNotification({
						type: 'warning',
						header: 'Внимание',
						text: 'С момента приема первичного вызова прошло более 24-х часов',
						//delay: 3000,
						id: 'callIsOverdue'
					});
				}
			}

            switch(conf.params.checkBy){
                case 'fio': {
                    nameField.setValue(conf.params.filterByName);
                    famField.setValue(conf.params.filterByFamily);
                    secNameField.setValue(conf.params.filterBySecName);
                    break;
                }
                case 'address': {
                    addressField.addressParams = {
                        Address_Zip: 0,
                        Address_begDate: 0,
                        Corpus: conf.params.korpField,
                        Country_id: 0,
                        Flat: conf.params.kvarField,
                        House: conf.params.domField,
                        KLCity_id: (allFields.dCityCombo.getSelectedRecord().get('KLAreaLevel_id') == 3)?allFields.dCityCombo.getSelectedRecord().get('Town_id') : 0,
                        KLRegion_id: allFields.dCityCombo.getSelectedRecord().get('Region_id'),
                        KLStreet_id: conf.params.recStreetCombo.get('KLStreet_id'),
                        KLSubRGN_id: 0,
                        KLTown_id: (allFields.dCityCombo.getSelectedRecord().get('KLAreaLevel_id') == 4)?allFields.dCityCombo.getSelectedRecord().get('Town_id') : 0
                    };
                    addrText = conf.params.recStreetCombo.get('AddressOfTheObject');

                    addrText += ', ' + conf.params.recStreetCombo.get('Socr_Nick') + ' ' +conf.params.recStreetCombo.get('StreetAndUnformalizedAddressDirectory_Name');

					if(conf.params.recSecondStreetCombo){
                    addrText += ', ' + conf.params.recSecondStreetCombo.get('Socr_Nick') + ' ' +conf.params.recSecondStreetCombo.get('StreetAndUnformalizedAddressDirectory_Name');
					}

                    addrText += (conf.params.domField) ? ', д.' + conf.params.domField : '';
                    addrText += (conf.params.korpField) ? ', к.' + conf.params.korpField : '';
                    addrText += (conf.params.kvarField) ? ', кв.' + conf.params.kvarField : '';

                    addressField.setValue(addrText);
                    break;
                }
				case 'DP_doDouble':{
					nameField.setValue(conf.params.filterByName);
					famField.setValue(conf.params.filterByFamily);
					secNameField.setValue(conf.params.filterBySecName);
					addressField.addressParams = {
						Address_Zip: 0,
						Address_begDate: 0,
						Corpus: conf.params.korpField,
						Country_id: 0,
						Flat: conf.params.kvarField,
						House: conf.params.domField,
						KLCity_id: conf.params.KLCity_id,
						KLRegion_id: conf.params.KLRegion_id,
						KLStreet_id: conf.params.KLStreet_id,
						KLSubRGN_id: 0,
						KLTown_id: conf.params.KLTown_id
					};
					addrText = conf.params.Adress_Name;

					addressField.setValue(addrText);
					break;
				}
				case 'CallUnderControl':{
					win.CallUnderControl = true;
					break;
				}
				default: {
					nameField.setValue(conf.params.filterByName);
					famField.setValue(conf.params.filterByFamily);
					secNameField.setValue(conf.params.filterBySecName);

					addressField.addressParams = {
						Address_Zip: 0,
						Address_begDate: 0,
						Corpus: conf.params.korpField,
						Country_id: 0,
						Flat: conf.params.kvarField,
						House: conf.params.domField,
						KLCity_id: (dCityCombo && (dCityCombo? dCityCombo.get('KLAreaLevel_id') == 3: false))?dCityCombo.get('Town_id') : 0,
						KLRegion_id: dCityCombo ? dCityCombo.get('Region_id') : 0,
						KLStreet_id: conf.params.recStreetCombo ? conf.params.recStreetCombo.get('KLStreet_id') : 0,
						KLSubRGN_id: 0,
						KLTown_id: (dCityCombo && dCityCombo.get('KLAreaLevel_id') == 4)?dCityCombo.get('Town_id') : 0
					};
					addrText = conf.params.recStreetCombo ? conf.params.recStreetCombo.get('AddressOfTheObject') : dCityCombo ? (dCityCombo.get('Socr_Nick') + ' ' + dCityCombo.get('Town_Name')) : '';

					addrText += conf.params.recStreetCombo ? (', ' + conf.params.recStreetCombo.get('Socr_Nick') + ' ' + conf.params.recStreetCombo.get('StreetAndUnformalizedAddressDirectory_Name')) : '';

					addrText += conf.params.recSecondStreetCombo ? (', ' + conf.params.recSecondStreetCombo.get('Socr_Nick') + ' ' + conf.params.recSecondStreetCombo.get('StreetAndUnformalizedAddressDirectory_Name')) : '';

					addrText += (conf.params.domField) ? ', д.' + conf.params.domField : '';
					addrText += (conf.params.korpField) ? ', к.' + conf.params.korpField : '';
					addrText += (conf.params.kvarField) ? ', кв.' + conf.params.kvarField : '';

					addressField.setValue(addrText);
				}
            };
			if(conf.params.CmpCallCard_id){
				win.CmpCallCard_id = conf.params.CmpCallCard_id
			}
			else{
				win.CmpCallCard_id = null;
			}
			win.filterCmpCalls = conf.params.filterCmpCalls;

			if(conf.showByDp){
				win.swSelectFirstSmpCallCard.getStore().getProxy().extraParams = {
					begDate: Ext.Date.format(new Date(Date.now()), 'd.m.Y H:i:s'),
					endDate: Ext.Date.format(new Date(Date.now()), 'd.m.Y H:i:s'),
					hours: 24,
					showByDp: true
				};
			}
			else{
				if (dCityCombo){
					var KLCity_id = (dCityCombo.get('KLAreaLevel_id') == 3)?dCityCombo.get('Town_id') : null,
						Town_id = (dCityCombo.get('KLAreaLevel_id') == 4)?dCityCombo.get('Town_id') : null;
				}

				win.swSelectFirstSmpCallCard.getStore().getProxy().extraParams = {
					cmpCallCardList: Ext.JSON.encode(conf.params.filterCmpCalls),
					begDate: Ext.Date.format(new Date(Date.now()), 'd.m.Y H:i:s'),
					endDate: Ext.Date.format(new Date(Date.now()), 'd.m.Y H:i:s'),
					Search_SurName: conf.params.filterByFamily,
					Search_FirName: conf.params.filterByName,
					KLCity_id: KLCity_id,
					Town_id: Town_id,
					KLStreet_id: conf.params.recStreetCombo ? conf.params.recStreetCombo.get('KLStreet_id') : null,
					UnformalizedAddressDirectory_id: conf.params.recStreetCombo ? conf.params.recStreetCombo.get('UnformalizedAddressDirectory_id') : null,
					hours: 24
				};
			};

			win.swSelectFirstSmpCallCard.getStore().load();

        });


		// Вынес инициализацию хранилища в начало метода, т.к. оно используется
		// в нескольких компонентах, инициализируемых ниже. Иначе возникает
		// ошибка undefined при обращении к этому хранилищу
		var selectFirstSmpCallCardStore = new Ext.data.JsonStore({
			autoLoad: false,
			numLoad: 0,
			storeId: 'selectFirstSmpCallCardStore',
			fields: [
				{name: 'CmpCallCard_id', type: 'int'},
				{name: 'CmpCallCard_prmDT', type: 'string'},
				{name: 'Person_id', type: 'int'},
				{name: 'Person_IsUnknown', type: 'int'},
				{name: 'PersonEvn_id', type: 'int'},
				{name: 'Server_id', type: 'int'},
				{name: 'Person_Surname', type: 'srting'},
				{name: 'Person_Firname', type: 'srting'},
				{name: 'Person_Secname', type: 'srting'},
				{name: 'Person_Age', type: 'int'},
				{name: 'Person_AgeText', type: 'string'},
				{name: 'pmUser_insID', type: 'srting'},
				{name: 'CmpCallCard_prmDate', type: 'string'},
				{name: 'CmpCallCard_Numv', type: 'string'},
				{name: 'CmpCallCard_Ngod', type: 'string'},
				{name: 'CmpCallCard_isLocked', type: 'int'},
				{name: 'CmpCallCard_Telf', type: 'string'},
				{name: 'Person_FIO', type: 'string'},
				{name: 'Person_Birthday', type: 'string'},
				{name: 'CmpReason_id', type: 'int'},
				{name: 'CmpReason_Name', type: 'string'},
				{name: 'CmpCallType_Name', type: 'string'},
				{name: 'CmpGroup_id', type: 'int'},
				{name: 'CmpGroupName_id', type: 'int'},
				{name: 'CmpLpu_Name', type: 'string'},
				{name: 'CmpDiag_Name', type: 'string'},
				{name: 'StacDiag_Name', type: 'string'},
				{name: 'SendLpu_Nick', type: 'string'},
				{name: 'PPDUser_Name', type: 'string'},
				{name: 'ServeDT', type: 'string'},
				{name: 'Sex_id', type: 'int'},
				{name: 'CmpCallCard_Ktov', type: 'string'},
				{name: 'CmpCallerType_id', type: 'int'},
				{name: 'PPDResult', type: 'string'},
				{name: 'Adress_Name', type: 'string'},
				{name: 'KLRgn_id', type: 'int', hidden: true},
				{name: 'KLSubRgn_id', type: 'int', hidden: true},
				{name: 'KLCity_id', type: 'int', hidden: true},
				{name: 'KLCity_Name', type: 'string', hidden: true},
				{name: 'KLTown_id', type: 'int', hidden: true},
				{name: 'KLTown_Name', type: 'string', hidden: true},
				{name: 'KLStreet_id', type: 'int', hidden: true},
				{name: 'KLStreet_FullName', type: 'string', hidden: true},
				{name: 'CmpCallCard_Dom', type: 'string', hidden: true},
				{name: 'CmpCallCard_Korp', type: 'string', hidden: true},
				{name: 'CmpCallCard_Kvar', type: 'string', hidden: true},
				{name: 'CmpCallCard_Comm', type: 'string', hidden: true},
				{name: 'CmpCallCard_Podz', type: 'string', hidden: true},
				{name: 'CmpCallCard_Etaj', type: 'string', hidden: true},
				{name: 'CmpCallCard_Kodp', type: 'string', hidden: true},
				{name: 'UnformalizedAddressDirectory_id', type: 'int', hidden: true},
				{name: 'UnformalizedAddressType_id', type: 'int', hidden: true},
				{name: 'UnformalizedAddressDirectory_Dom', type: 'string', hidden: true},
				{name: 'UnformalizedAddressDirectory_Name', type: 'string', hidden: true},
				{name: 'CmpCallCardStatusType_id', type: 'int'},
				{name: 'CmpCallCardEventType_Name', type: 'string'},
				{name: 'EmergencyTeam_Num', type: 'string'},
				{name: 'EmergencyTeam_id', type: 'int'},
				{name: 'CmpCallPlaceType_id', type: 'int', hidden: true},
				{name: 'CmpCallCard_IsExtra', type: 'int', hidden: true},
				{name: 'LpuBuilding_id', type: 'int', hidden: true},
				{name: 'Lpu_ppdid', type: 'int', hidden: true},
				{name: 'MedService_id', type: 'int', hidden: true},
				{name: 'CmpCallCard_IsPoli', type: 'int', hidden: true},
				{name: 'CmpCallCard_IsPassSSMP', type: 'int', hidden: true},
				{name: 'Lpu_smpid', type: 'int', hidden: true},
				{name: 'StreetAndUnformalizedAddressDirectory_id', type: 'string'}
			],
			sorters: [
				{
					direction: 'DESC',
					property: 'CmpCallCard_prmDT',
					transform: function(val){
						return Ext.Date.parse(val,"d.m.Y H:i:s")
					}
				},
			],
			proxy: {
				limitParam: undefined,
				startParam: undefined,
				paramName: undefined,
				pageParam: undefined,
				type: 'ajax',
				url: '/?c=CmpCallCard4E&m=loadSMPCmpCallCardsList',
				reader: {
					type: 'json',
					successProperty: 'success',
					idProperty: 'CmpCallCard_id',
					root: 'data'
				},
				actionMethods: {
					create: 'POST',
					read: 'POST',
					update: 'POST',
					destroy: 'POST'
				},
				filters: [{
						property: 'CmpGroup_id',
						value: 2 || 3
					}],
				extraParams: {
					begDate: Ext.Date.format(new Date(Date.now()), 'd.m.Y H:i:s'),
					endDate: Ext.Date.format(new Date(Date.now()), 'd.m.Y H:i:s'),
					hours: 24
				}
			},
			listeners: {
				load: function(){
					win.swSelectFirstSmpCallCard.getSelectionModel().select(0);
					win.filterResults();
					if(win.CallUnderControl)
					{
						var grid = win.swSelectFirstSmpCallCard,
							rec = grid.getSelectionModel().getSelection()[0];
						var cell = win.swSelectFirstSmpCallCard.getSelectionModel().getSelection()[0];
						//if (Ext.StoreManager.lookup('selectFirstSmpCallCardStore')

						if(cell) {
							win.enabledAll();
							if (cell.data.CmpCallCardStatusType_id.inlist(['4', '5', '6', '9', '16'])) {
								win.down('button[refId=doublenewCall]').setDisabled(true);
								win.down('button[refId=feelBadlyCall]').setDisabled(true);
								win.down('button[refId=rejectCall]').setDisabled(true);
							}
							if (!cell.data.CmpCallCardStatusType_id.inlist(['4', '6'])) {
								win.down('button[refId=doubleCall]').setDisabled(true);
							}
							win.toggleSearchOptionsPage();
						}
						else
							return false;
					}
				}
			}
		});

        var address_combo =  Ext.create('sw.AddressCombo', {
            fieldLabel: 'Адрес',
            labelWidth: 60,
            flex: 1,
            labelAlign: 'right',
            name: 'filterByAddress',
            addressParams: {
                Address_Zip: 0,
                Address_begDate: 0,
                Corpus: 0,
                Country_id: ( getRegionNick() == 'kz' ) ? 398 : 643,
                Flat: 0,
                House: 0,
                KLCity_id: 0,
                KLRegion_id: getGlobalOptions().region.number,
                KLStreet_id: 0,
                KLSubRGN_id: 0,
                KLTown_id: 0
            },
            onTrigger2Click : function(){
                if(!this.getValue()) return;

                for(var addrEl in this.addressParams){

                    this.addressParams[addrEl] = 0;
                }
                this.setValue('');
                this.addressParams =  {
                    Address_Zip: 0,
                    Address_begDate: 0,
                    Corpus: 0,
                    Country_id: ( getRegionNick() == 'kz' ) ? 398 : 643,
                    Flat: 0,
                    House: 0,
                    KLCity_id: 0,
                    KLRegion_id: getGlobalOptions().region.number,
                    KLStreet_id: 0,
                    KLSubRGN_id: 0,
                    KLTown_id: 0
                };
                win.filterResults(this);
            },
            onTrigger1Click : function(){
                var field = this;

                field.showAddressWindow(
                    {
                        Country_id: field.addressParams.Country_id,
                        KLRegion_id: field.addressParams.KLRegion_id ? field.addressParams.KLRegion_id : getGlobalOptions().region.number,
                        Address_Zip: field.addressParams.Address_Zip,
                        Address_begDate: field.addressParams.Address_begDate,
                        Corpus: field.addressParams.Corpus,
                        Country_id: field.addressParams.Country_id,
                        Flat: field.addressParams.Flat,
                        House: field.addressParams.House,
                        KLCity_id: field.addressParams.KLCity_id,
                        KLStreet_id: field.addressParams.KLStreet_id,
                        KLSubRGN_id: field.addressParams.KLSubRGN_id,
                        KLTown_id: field.addressParams.KLTown_id
                    },
                    function(data){

                        field.setValue(data.full_address);

                        field.addressParams = data;

                        if((!data.KLCity_id || !data.KLTown_id) && !data.KLStreet_id) return;

                        win.filterResults(field);
                    }
                );
            }

        });

		win.selectOptions = Ext.create('Ext.container.Container', {
			hidden: true,
			layout: {
				type: 'vbox',
				align: 'center',
				pack: 'center'
			},
			items: [
					{
						xtype: 'button',
						style: 'font-size: 40px;',
						width: '90%',
						padding: '10, 0',
						text: 'Справочный вызов',
						name: 'informCall',
						refId: 'informCall'
					},
					{
						xtype: 'button',
						text: 'Дублирующий вызов',
						width: '90%',
						padding: '10, 0',
						name: 'doublenewCall',
						refId: 'doublenewCall'
					},
//						{
//							xtype: 'radiofield',
//							boxLabel: 'В помощь',
//							name: 'helpCall'
//						},
//						{
//							xtype: 'radiofield',
//							boxLabel: 'Просят ускорить',
//							name: 'boostCall'
//						},
					{
						xtype: 'button',
						width: '90%',
						padding: '10, 0',
						text: 'Состояние ухудшилось',
						name: 'feelBadlyCall',
						refId: 'feelBadlyCall'
					},
					{
						xtype: 'button',
						width: '90%',
						padding: '10, 0',
						text: 'Оформить отказ',
						name: 'rejectCall',
						refId: 'rejectCall'
					},
					{
						xtype: 'button',
						width: '90%',
						padding: '10, 0',
						text: 'Повторный вызов',
						name: 'doubleCall',
						refId: 'doubleCall'
					},
					{
						xtype: 'button',
						width: '90%',
						padding: '10, 0',
						text: 'Вызов на третье лицо',
						name: 'thirdPersonCall',
						refId: 'thirdPersonCall',
						hidden: true
					}

				],
				defaults: {
				  listeners: {
					click: function (cmp) {
						var radioAction = cmp.name,
							grid = this.swSelectFirstSmpCallCard,
							selRec = grid.getSelectionModel().getSelection()[0];

						if (selRec.data.CmpCallCardStatusType_id != 19) {
							selRec.raw.CmpCallCard_storDT = null;
						}

						if(!win.CallUnderControl){
							if( (new Date - Ext.Date.parse(selRec.get('CmpCallCard_prmDT'), 'd.m.Y H:i:s'))/1000/3600 > 24 ){
								Ext.Msg.alert('Ошибка', 'Выбран вызов с некорректной датой вызова');
								return false;
							};
						};

						switch(radioAction){
							case 'doublenewCall': {
								this.fireEvent('doublenewCall', selRec);
								this.hide();
							break;};
							case 'informCall': {
								this.fireEvent('informCall', selRec);
								this.hide();
							break;};
							case 'rejectCall': {
								this.fireEvent('rejectCall', selRec);
								this.hide();
							break;};
							case 'doubleCall': {
								this.fireEvent('doubleCall', selRec);
								this.hide();
							break;};
							case 'helpCall': {
								this.fireEvent('helpCall', selRec);
								this.hide();
							break;};
							case 'boostCall': {
								this.fireEvent('boostCall', selRec);
								this.hide();
							break;};
							case 'feelBadlyCall': {
								this.fireEvent('feelBadlyCall', selRec);
								this.hide();
							break;};
							case 'thirdPersonCall': {
								this.fireEvent('thirdPersonCall', selRec);
								this.hide();
							break;};
						}
					  }.bind(this)
					}
				}
		});

		win.topTbar = Ext.create('Ext.toolbar.Toolbar',{
			region: 'north',
			flex: 1,
			items: [
			{
				xtype: 'fieldset',
				padding: '0 2 4 2',
				collapsible: true,
				title: 'Фильтры',
				layout: 'hbox',
				flex: 1,
				items: [
					address_combo,
					{
						xtype: 'transFieldDelbut',
						autocompleteField: false,
						fieldLabel: 'Фамилия',
						name: 'filterByFamily',
						typeAheadDelay: 1,
						labelWidth: 50,
						width: 200,
						displayField: 'Person_Surname',
						storeName: 'selectFirstSmpCallCardStore',
						enableKeyEvents : true,
						listeners: {
							keypress: function(c, e, o){
								if ( (e.getKey() == 13)){
									win.filterResults(this);
								}
							},
							change: function(c){
								//win.filterResults(this);
							}
						}
					},
					{
						xtype: 'transFieldDelbut',
						autocompleteField: false,
						fieldLabel: 'Имя',
						name: 'filterByName',
						typeAheadDelay: 1,
						labelWidth: 40,
						width: 200,
						displayField: 'Person_Firname',
						storeName: 'selectFirstSmpCallCardStore',
						enableKeyEvents : true,
						listeners: {
							keypress: function(c, e, o){
								if ( (e.getKey() == 13)){
									win.filterResults(this);
								}
							},
							change: function(c){
								//win.filterResults(this);
							}
						}
					},
					{
						xtype: 'transFieldDelbut',
						autocompleteField: false,
						fieldLabel: 'Отчество',
						name: 'filterBySecName',
						typeAheadDelay: 1,
						displayField: 'Person_Secname',
						storeName: 'selectFirstSmpCallCardStore',
						enableKeyEvents : true,
						listeners: {
							keypress: function(c, e, o){
								if ( (e.getKey() == 13)){
									win.filterResults(this);
								}
							},
							change: function(c){
								//win.filterResults(this);
							}
						}
					},
					{
						xtype: 'button',
						text: 'Найти',
						iconCls: 'search16',
						margin: '0 0 0 10',
						handler: function() {
							win.filterResults(this);
						}
					},
					{
						xtype: 'button',
						text: 'Сброс',
						iconCls: 'resetsearch16',
						margin: '0 0 0 10',
						handler: function() {
							win.resetFilter();
						}
					}
				]
			}]
		})

		win.chooseBtn = Ext.create('Ext.button.Button', {
			text: 'Выбрать',
			iconCls: 'ok16',
			refId: 'okButton',
			disabled: false,
			handler: function(){
				if(conf.showByDp){
					var grid = win.swSelectFirstSmpCallCard,
						rec = grid.getSelectionModel().getSelection()[0];
					win.fireEvent('doubleCall', rec);
					win.hide();
				}else{

					var cell = win.swSelectFirstSmpCallCard.getSelectionModel().getSelection()[0];
					if(cell) {
						win.enabledAll();
						if (cell.data.CmpCallCardStatusType_id.inlist(['4', '5', '6', '9', '16'])) {
							win.down('button[refId=doublenewCall]').setDisabled(true);
							win.down('button[refId=feelBadlyCall]').setDisabled(true);
							win.down('button[refId=rejectCall]').setDisabled(true);
						}
						if (!cell.data.CmpCallCardStatusType_id.inlist(['4', '6'])) {
							win.down('button[refId=doubleCall]').setDisabled(true);
						}
						win.toggleSearchOptionsPage();
					}
					else
						return false;
				}

			}
		})

		win.swSelectFirstSmpCallCard = Ext.create('Ext.grid.Panel', {
			flex: 1,
			stripeRows: true,
			refId: 'swSelectFirstSmpCallCard',
			viewConfig: {
				loadingText: 'Загрузка'
			},
			listeners: {
				itemClick: function(cmp, record, item, index, e, eOpts ){
					var b = win.down('button[refId=selRec]');
					if (b){
						b.enable();
					}
				},
				cellkeydown: function(cmp, td, cellIndex, record, tr, rowIndex, e, eOpts){
					 if (e.getKey() == e.ENTER){
						 if(conf.showByDp){
							 var grid = win.swSelectFirstSmpCallCard,
								 rec = grid.getSelectionModel().getSelection()[0];
							 win.fireEvent('doubleCall', rec);
							 win.hide();
						 }else{
							 win.enabledAll();
							 if (record.data.CmpCallCardStatusType_id.inlist(['4','5','6','9','16'])) {
								 win.down('button[refId=doublenewCall]').setDisabled( true );
								 win.down('button[refId=feelBadlyCall]').setDisabled( true );
								 win.down('button[refId=rejectCall]').setDisabled( true );
							 }
							 if (!record.data.CmpCallCardStatusType_id.inlist(['4','6'])) {
								 win.down('button[refId=doubleCall]').setDisabled( true );
							 }
							 win.toggleSearchOptionsPage();
						 }
					 }
				},
				celldblclick: function( cmp, td, cellIndex, record, tr, rowIndex, e, eOpts ){
					if(conf.showByDp){
						var grid = win.swSelectFirstSmpCallCard,
							rec = grid.getSelectionModel().getSelection()[0];
						win.fireEvent('doubleCall', rec);
						win.hide();
					}else{
						win.enabledAll();
						if (record.data.CmpCallCardStatusType_id.inlist(['4','5','6','9','16'])) {
							win.down('button[refId=doublenewCall]').setDisabled( true );
							win.down('button[refId=feelBadlyCall]').setDisabled( true );
							win.down('button[refId=rejectCall]').setDisabled( true );
						}
						if (!record.data.CmpCallCardStatusType_id.inlist(['4','6'])) {
							win.down('button[refId=doubleCall]').setDisabled( true );
						}
						win.toggleSearchOptionsPage();
					}
				}
			},
			store: selectFirstSmpCallCardStore,
			columns: [
				{ dataIndex: 'CmpCallCard_prmDT', text: 'Дата и время вызова', width:125, hideable: false  },
				{ dataIndex: 'Person_Surname', text: 'Фамилия', flex: 1, hideable: false  },
				{ dataIndex: 'Person_Firname', text: 'Имя', flex: 1, hideable: false  },
				{ dataIndex: 'Person_Secname', text: 'Отчество', flex: 1, hideable: false  },
				{ dataIndex: 'Person_AgeText', text: 'Возраст', flex: 1, hideable: false },
				{ dataIndex: 'CmpCallCard_Numv', text: '№ вызова (день)', width: 120, hideable: false  },
				{ dataIndex: 'CmpReason_Name', text: 'Повод', flex: 2, hideable: false },
				{ dataIndex: 'Adress_Name', text: 'Адрес', flex: 3, hideable: false  },
				{ dataIndex: 'CmpCallCardEventType_Name', text: 'Событие', flex: 2, hideable: false  },
				{ dataIndex: 'EmergencyTeam_Num', text: 'Бригада', flex: 1, hideable: false  }
			]
		});

		//отправляем сборку
		win.configComponents = {
			top: win.topTbar,
			center: [win.swSelectFirstSmpCallCard,win.selectOptions],
			leftButtons: win.chooseBtn
		};

		win.callParent(arguments);
	}
})

