var constants = {
	socketPort: 9991,
	httpPort: 9992
};

for (var key in constants) {
	exports[key] = constants[key];
};