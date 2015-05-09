(function() {
	var app = angular.module('dragonasi', ['player-selection', 'waiting-players', 'play']);

	app.controller('DragonasiController', function($scope, $rootScope) {
		$rootScope.step = 1;

		window.ws.init($rootScope, function() {
			window.ws.send('{"command": "connect"}');
		});
	});
}());