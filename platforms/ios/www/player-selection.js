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
				this.selectedPlayer = false;
				this.connectedPlayers = [];

				this.select = function(player) {
					if (!this.connectedPlayers[player]) {
						this.selectedPlayer = player;
						window.ws.send('{"command": "setGroup", "group": ' + this.selectedPlayer + '}');
					}
				};
				this.next = function() {
					window.ws.send('{"command": "ready"}');
					$rootScope.step = 2;
				};

				this.canGoNext = function() {
					if (!this.selectedPlayer) return false;
					for (var i=1;i<this.connectedPlayers.length-1;i++) {
						if (!this.connectedPlayers[i] && this.connectedPlayers[i+1]) return false;
					}
					if (!this.connectedPlayers[1]) return false;
					return true;
				};

				this.isSelected = function(player) {
					return this.selectedPlayer === player;
				};

				this.connected = function(player) {
					this.connectedPlayers[player] = true;
					$scope.$apply();
				};

				this.disconnected = function(player) {
					delete this.connectedPlayers[player];
					$scope.$apply();
				};

				this.isConnected = function(player) {
					return this.connectedPlayers[player]?true:false;
				};

				var that = this;

				$rootScope.$on('groupSelected', function (event, group) {
		             //do stuff
		             that.connected(group);
		        });

				$rootScope.$on('groupDisconnected', function (event, group) {
		             //do stuff
		             that.disconnected(group);
		        });

		        

			},
			controllerAs: 'playerSelection'
		}
	});

}());