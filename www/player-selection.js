(function() {
	var app = angular.module('player-selection', [ ]);

	app.directive('playerSelection', function() {
		return {
			restrict: 'E',
			templateUrl: 'player-selection.html',
			controller: function() {
				this.title = "Pasul 1. Alege grupul.";
				this.players = [
					1, 2, 3, 4, 5, 6
				];
				this.selectedPlayer = false;

				this.select = function(player) {
					this.selectedPlayer = player;
				};

				this.playerWasSelected = function() {
					return this.selectedPlayer?true:false;
				};

				this.isSelected = function(player) {
					return this.selectedPlayer === player;
				}
			},
			controllerAs: 'playerSelection'
		}
	});

}());