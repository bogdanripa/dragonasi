(function() {
	var app = angular.module('connecting', [ ]);

	app.directive('connecting', function() {
		return {
			restrict: 'E',
			templateUrl: 'connecting.html',
			controller: function($scope, $rootScope) {
				this.title = "Se conecteaza...";
			},
			controllerAs: 'connecting'
		}
	});

}());