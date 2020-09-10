ncaLayer = {
	webSocket: null,
	heartbeat_interval: null,
	heartbeat_msg: '--heartbeat--',
	callback: null,
	isDebug: 1,
	log: function() {
		if (this.isDebug) {
			console.log.apply(console, arguments);
		}
	},
	connect: function(onConnect) {
		ncaLayer.webSocket = new WebSocket('wss://127.0.0.1:13579/');

		ncaLayer.webSocket.onopen = function (event) {
			if (ncaLayer.heartbeat_interval === null) {
				ncaLayer.heartbeat_interval = setInterval(ncaLayer.pingLayer, 2000);
			}
			ncaLayer.log("Connection opened");

			if (onConnect && typeof onConnect == 'function') {
				onConnect();
			}
		};

		ncaLayer.webSocket.onclose = function (event) {
			if (event.wasClean) {
				ncaLayer.log('connection has been closed');
			} else {
				ncaLayer.log('Connection error');
				ncaLayer.openDialog();
			}
			ncaLayer.log('Code: ' + event.code + ' Reason: ' + event.reason);
		};

		ncaLayer.webSocket.onmessage = function (event) {
			if (event.data === ncaLayer.heartbeat_msg) {
				return;
			}

			var result = JSON.parse(event.data);

			if (result != null) {
				var rw = {
					result: result['result'],
					secondResult: result['secondResult'],
					errorCode: result['errorCode'],
					getResult: function () {
						return this.result;
					},
					getSecondResult: function () {
						return this.secondResult;
					},
					getErrorCode: function () {
						return this.errorCode;
					}
				};
				if (ncaLayer.callback && typeof ncaLayer.callback == 'function') {
					ncaLayer.callback(rw);
				}
			}
			ncaLayer.log(event);
		};
	},
	openDialog: function() {
		if (confirm("Ошибка при подключении к NCALayer. Убедитесь что программа запущена и нажмите ОК для перезагрузки страницы") === true) {
			location.reload();
		}
	},
	pingLayer: function() {
		ncaLayer.log("pinging...");
		try {
			ncaLayer.webSocket.send(ncaLayer.heartbeat_msg);
		} catch (e) {
			clearInterval(ncaLayer.heartbeat_interval);
			ncaLayer.heartbeat_interval = null;
			ncaLayer.log("Closing connection. Reason: " + e.message);
			ncaLayer.webSocket.close();
		}
	},
	browseKeyStore: function(storageName, fileExtension, currentDirectory, callBack) {
		var browseKeyStore = {
			"method": "browseKeyStore",
			"args": [storageName, fileExtension, currentDirectory]
		};
		ncaLayer.callback = callBack;
		ncaLayer.webSocket.send(JSON.stringify(browseKeyStore));
	},
	checkNCAVersion: function(callBack) {
		var checkNCAVersion = {
			"method": "browseKeyStore",
			"args": [storageName, fileExtension, currentDirectory]
		};
		ncaLayer.callback = callBack;
		ncaLayer.webSocket.send(JSON.stringify(checkNCAVersion));
	},
	loadSlotList: function(storageName, callBack) {
		var loadSlotList = {
			"method": "loadSlotList",
			"args": [storageName]
		};
		ncaLayer.callback = callBack;
		ncaLayer.webSocket.send(JSON.stringify(tryCount));
	},
	showFileChooser: function(fileExtension, currentDirectory, callBack) {
		var showFileChooser = {
			"method": "showFileChooser",
			"args": [fileExtension, currentDirectory]
		};
		ncaLayer.callback = callBack;
		ncaLayer.webSocket.send(JSON.stringify(showFileChooser));
	},
	getKeys: function(storageName, storagePath, password, type, callBack) {
		var getKeys = {
			"method": "getKeys",
			"args": [storageName, storagePath, password, type]
		};
		ncaLayer.callback = callBack;
		ncaLayer.webSocket.send(JSON.stringify(getKeys));
	},
	getNotAfter: function(storageName, storagePath, alias, password, callBack) {
		var getNotAfter = {
			"method": "getNotAfter",
			"args": [storageName, storagePath, alias, password]
		};
		ncaLayer.callback = callBack;
		ncaLayer.webSocket.send(JSON.stringify(getNotAfter));
	},
	setLocale: function(lang) {
		var setLocale = {
			"method": "setLocale",
			"args": [lang]
		};
		ncaLayer.webSocket.send(JSON.stringify(setLocale));
	},
	getNotBefore: function(storageName, storagePath, alias, password, callBack) {
		var getNotBefore = {
			"method": "getNotBefore",
			"args": [storageName, storagePath, alias, password]
		};
		ncaLayer.callback = callBack;
		ncaLayer.webSocket.send(JSON.stringify(getNotBefore));
	},
	getSubjectDN: function(storageName, storagePath, alias, password, callBack) {
		var getSubjectDN = {
			"method": "getSubjectDN",
			"args": [storageName, storagePath, alias, password]
		};
		ncaLayer.callback = callBack;
		ncaLayer.webSocket.send(JSON.stringify(getSubjectDN));
	},
	getIssuerDN: function(storageName, storagePath, alias, password, callBack) {
		var getIssuerDN = {
			"method": "getIssuerDN",
			"args": [storageName, storagePath, alias, password]
		};
		ncaLayer.callback = callBack;
		ncaLayer.webSocket.send(JSON.stringify(getIssuerDN));
	},
	getRdnByOid: function(storageName, storagePath, alias, password, oid, oidIndex, callBack) {
		var getRdnByOid = {
			"method": "getRdnByOid",
			"args": [storageName, storagePath, alias, password, oid, oidIndex]
		};
		ncaLayer.callback = callBack;
		ncaLayer.webSocket.send(JSON.stringify(getRdnByOid));
	},
	signPlainData: function(storageName, storagePath, alias, password, dataToSign, callBack) {
		var signPlainData = {
			"method": "signPlainData",
			"args": [storageName, storagePath, alias, password, dataToSign]
		};
		ncaLayer.callback = callBack;
		ncaLayer.webSocket.send(JSON.stringify(signPlainData));
	},
	verifyPlainData: function(storageName, storagePath, alias, password, dataToVerify, base64EcodedSignature, callBack) {
		var verifyPlainData = {
			"method": "verifyPlainData",
			"args": [storageName, storagePath, alias, password, dataToVerify, base64EcodedSignature]
		};
		ncaLayer.callback = callBack;
		ncaLayer.webSocket.send(JSON.stringify(verifyPlainData));
	},
	createCMSSignature: function(storageName, storagePath, alias, password, dataToSign, attached, callBack) {
		var createCMSSignature = {
			"method": "createCMSSignature",
			"args": [storageName, storagePath, alias, password, dataToSign, attached]
		};
		ncaLayer.callback = callBack;
		ncaLayer.webSocket.send(JSON.stringify(createCMSSignature));
	},
	createCMSSignatureFromFile: function(storageName, storagePath, alias, password, filePath, attached, callBack) {
		var createCMSSignatureFromFile = {
			"method": "createCMSSignatureFromFile",
			"args": [storageName, storagePath, alias, password, filePath, attached]
		};
		ncaLayer.callback = callBack;
		ncaLayer.webSocket.send(JSON.stringify(createCMSSignatureFromFile));
	},
	verifyCMSSignature: function(sigantureToVerify, signedData, callBack) {
		var verifyCMSSignature = {
			"method": "verifyCMSSignature",
			"args": [sigantureToVerify, signedData]
		};
		ncaLayer.callback = callBack;
		ncaLayer.webSocket.send(JSON.stringify(verifyCMSSignature));
	},
	verifyCMSSignatureFromFile: function(signatureToVerify, filePath, callBack) {
		var verifyCMSSignatureFromFile = {
			"method": "verifyCMSSignatureFromFile",
			"args": [signatureToVerify, filePath]
		};
		ncaLayer.callback = callBack;
		ncaLayer.webSocket.send(JSON.stringify(verifyCMSSignatureFromFile));
	},
	signXml: function(storageName, storagePath, alias, password, xmlToSign, callBack) {
		var signXml = {
			"method": "signXml",
			"args": [storageName, storagePath, alias, password, xmlToSign]
		};
		ncaLayer.callback = callBack;
		ncaLayer.webSocket.send(JSON.stringify(signXml));
	},
	signXmlByElementId: function(storageName, storagePath, alias, password, xmlToSign, elementName, idAttrName, signatureParentElement, callBack) {
		var signXmlByElementId = {
			"method": "signXmlByElementId",
			"args": [storageName, storagePath, alias, password, xmlToSign, elementName, idAttrName, signatureParentElement]
		};
		ncaLayer.callback = callBack;
		ncaLayer.webSocket.send(JSON.stringify(signXmlByElementId));
	},
	verifyXml: function(xmlSignature, callBack) {
		var verifyXml = {
			"method": "verifyXml",
			"args": [xmlSignature]
		};
		ncaLayer.callback = callBack;
		ncaLayer.webSocket.send(JSON.stringify(verifyXml));
	},
	verifyXmlById: function(xmlSignature, xmlIdAttrName, signatureElement, callBack) {
		var verifyXml = {
			"method": "verifyXml",
			"args": [xmlSignature, xmlIdAttrName, signatureElement]
		};
		ncaLayer.callback = callBack;
		ncaLayer.webSocket.send(JSON.stringify(verifyXml));
	},
	getHash: function(data, digestAlgName, callBack) {
		var getHash = {
			"method": "getHash",
			"args": [data, digestAlgName]
		};
		ncaLayer.callback = callBack;
		ncaLayer.webSocket.send(JSON.stringify(getHash));
	}
};