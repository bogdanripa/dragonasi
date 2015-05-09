(function() {
	var app = angular.module('waiting-players', [ ]);

	app.directive('waitingPlayers', function() {
		return {
			restrict: 'E',
			templateUrl: 'waiting-players.html',
			controller: function($scope, $rootScope) {

				this.title = "Pasul 2. Așteptăm pe toată lumea.";

				this.next = function() {
					$rootScope.step = 3;
					$scope.$apply();
				};

				this.back = function() {
					$rootScope.step = 1;
				};

				var that = this;

				$rootScope.$on('allPlayersReady', function () {
		             //do stuff
		             that.next();
		        });

			},
			controllerAs: 'waitingPlayers'
		}
	});

}());