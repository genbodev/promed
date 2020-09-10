var socket = {},
	connection,
	db,
	controlNumber;
	
	
//Функция обработки ошибок при работе с транзакцией WebSql
function onError(tx, error) {
  console.log(error.message);
  $.mobile.hidePageLoadingMsg();
  alert('Ошибка инициализации. Обратитесь к администратору');
}
function processServerError(err,msg,disconnect) {
	alert( (typeof msg == 'string')?msg: 'При запросе на сервере произошла ошибка. Обратитесь к администратору' );
	console.log(error);
	if ((typeof disconnect != 'undefined')&&(disconnect)) {
		disconnect();
	}
}
//Основная функция соединения
function connect(cb) {
	
	function logg() {
		console.log(socket);
	}
	
	var uninitialiseEventTimeout = setTimeout(function(){
		if (!socket.connected) {
			cb(false,'Инициализация невозможна. Нет соединения с сервером. Проверьте канал связи');
		}
	},15000);
	
	socket = io(connectHost);
	
	socket.on('connect', function () {
		socket.emit('authentification',document.cookie, navigator.userAgent, function(data,error) {
			
			if (error) {
				processServerError(error,'В процессе авторизации на сервере произошла ошибка! Обратитесь к администратору',false)
				return;
			}
			
			try {
				response = JSON.parse(data);
				if ((typeof response.action !='undefined')&&(response.action = 'logout')) {
					logout();
					return false;
				}
				var controlNumber = response.controlNumber;
			}
			catch(e) {
				alert('Процесс авторизации завершился ошибкой');
				console.log(e)
			}
			db = openDatabase('diag', '1.0', 'diagnoses', 4 * 1024 * 1024);
			//$.mobile.showPageLoadingMsg('a','Проверка справочников',true);
		
			db.transaction(function(tx) {
				tx.executeSql("CREATE TABLE IF NOT EXISTS Diags (id INTEGER unique, Code1 text, Code2 text,Code3 text,Code4 text,Name text)", [],function(tx) { 
					console.log('Diags created'); 
				},onError);
				tx.executeSql("CREATE TABLE IF NOT EXISTS CallCards (id INTEGER unique, Data text,ViewData text, Sended integer,Date text, SelectIndexes text)", [],function(tx) { 
					console.log('CallCards created'); 
				},onError);
				if (localStorage.getItem('EmergencyTeam_id') != $('#EmergencyTeam_id').html()) {
					tx.executeSql("DELETE FROM CallCards ", [],function(tx) { 
						console.log('CallCards deleted'); 
						localStorage.setItem('EmergencyTeam_id',$('#EmergencyTeam_id').html());
					},onError);
				} else {
					updateClosedCardsView();
				}
				tx.executeSql('SELECT COUNT(*) as count FROM Diags', [], function (tx, results) {
					var existedControlNumber = results.rows.item(0).count;
					console.log(existedControlNumber);
					
					if ((typeof controlNumber == 'undefined')||(controlNumber != existedControlNumber)){
						//$.mobile.showPageLoadingMsg('a','Загрузка справочников. Пожалуйста, ждите...',true);
						socket.emit('getDiags', function(data,error) {
							clearTimeout(uninitialiseEventTimeout);
			
			
							if (error) {
								processServerError(error,'В процессе получения справочника на сервере произошла ошибка! Обратитесь к администратору',false)
								return;
							}
							
							try {
								diags = JSON.parse(data);
								db.transaction(function(tx1){
									tx1.executeSql('DELETE FROM Diags', [], function (tx, results) {
										db.transaction(function(tx2){
											for (i=0; i<diags.length; i++) {
												code1 = diags[i].code[0];
												code2 = code1+diags[i].code[1];
												code3 = code2+diags[i].code[2];
												code4 = (typeof diags[i].code[4] == 'undefined')?(code3):(code3+diags[i].code[4]);
												tx2.executeSql('insert into diags (id,Code1,Code2,Code3,Code4,Name) VALUES (?,?,?,?,?,?)', [diags[i].id,code1,code2,code3,code4,diags[i].name], function (tx, results) {
												},onError);
											};
											//$.mobile.hidePageLoadingMsg()
											cb(true);
										});
										db.transaction(function(tx3){
											tx3.executeSql('SELECT COUNT(*) as count FROM Diags', [], function (tx, results) {
												console.log(results.rows.item(0).count);
											}, onError);
										})
									},onError);
								})
							} catch(e) {
								console.log({'Parse Diags Error':e});
							}
						});
					} else {
						clearTimeout(uninitialiseEventTimeout);
						cb(true);
						//$.mobile.hidePageLoadingMsg()
					}
				}, onError);
			});
		});	
	});
	socket.on('logout', function () {
		console.log('logout');
		logout();
	});
	socket.on('NewCallCard', function (data) {
		console.log('NewCallCard');
		addNewCard(data);
	});
	
}
function disconnect() {
	if (socket && (typeof socket.disconnect == 'function')) {
		socket.disconnect();
	}
}
function logout(){
	try {
		db.transaction(function(tx) {
			tx.executeSql("DELETE FROM CallCards ", [],function(tx) { 
				console.log('CallCards deleted'); 
			},onError);
		});
	} catch (e) {
		console.log(e);
	} finally {
		location.replace(location.origin+'/?c=main&m=Logout');
	}	
}

function setStatus(statusId) {
	if (socket.connected==true) {
		socket.emit('setStatus',statusId, function(data,error){
			
			if (error) {
				processServerError(error,'В процессе установки статуса на сервере произошла ошибка! Обратитесь к администратору',false)
				return;
			}
			
			console.log(data);
		})
	}
}



