(function() {
	var app = angular.module('player-selection', [ ]);

	app.directive('playerSelection', function() {
		return {
			restrict: 'E',
			templateUrl: 'player-selection.html',
			controller: function($scope, $rootScope) {
				this.title = "Pasul 1. Alege grupul.";
				this.players = [
					1, 2, 3, 4, 5, 6
				];

				$rootScope.selectedPlayer = false;
				$rootScope.connectedPlayers = [];

				this.select = function(player) {
					if (!$rootScope.connectedPlayers[player]) {
						$rootScope.selectedPlayer = player;
						window.ws.send('{"command": "setGroup", "group": ' + $rootScope.selectedPlayer + '}');
					}
				};
				this.next = function() {
					window.ws.send('{"command": "ready"}');
					$rootScope.step = 2;
				};

				this.canGoNext = function() {
					if (!$rootScope.selectedPlayer) return false;
					for (var i=1;i<$rootScope.connectedPlayers.length-1;i++) {
						if (!$rootScope.connectedPlayers[i] && $rootScope.connectedPlayers[i+1]) return false;
					}
					if (!$rootScope.connectedPlayers[1]) return false;
					return true;
				};

				this.isSelected = function(player) {
					return $rootScope.selectedPlayer === player;
				};

				this.connected = function(player) {
					$rootScope.connectedPlayers[player] = true;
					$scope.$apply();
				};

				this.disconnected = function(player) {
					delete $rootScope.connectedPlayers[player];
					$scope.$apply();
				};

				this.isConnected = function(player) {
					return $rootScope.connectedPlayers[player]?true:false;
				};

				var that = this;

				$rootScope.$on('groupSelected', function (event, group) {
		             that.connected(group);
		        });

				$rootScope.$on('groupDisconnected', function (event, group) {
		             that.disconnected(group);
		        });
			},
			controllerAs: 'playerSelection'
		}
	});

}());