(function() {
	var app = angular.module('play', [ ]);

	app.directive('play', function() {
		return {
			restrict: 'E',
			templateUrl: 'play.html',
			controller: function($scope, $rootScope) {

				this.title = "Pe locuri... Fiți gata...";
				this.answers = [];

				this.back = function() {
					$rootScope.step = 1;
				};

				this.ask = function(q) {
					this.lastAnswer = Date.now()-10000;

					this.title = q.question;
					this.answers = q.answers;
					this.selectedAnswer = null;
					$scope.$apply();
				};

				this.select = function(answer) {
					if (this.lastAnswer+5000 > Date.now()) {
						return;
					}
					this.lastAnswer = Date.now();

					this.selectedAnswer = answer;
					window.ws.send('{"command": "answer", "answer": ' + this.selectedAnswer + '}');
				};

				this.isSelected = function(answer) {
					return answer === this.selectedAnswer;
				};

				var that = this;

				$rootScope.$on('restart', function () {
		             //do stuff
		             that.back();
		        });

				$rootScope.$on('question', function (event, q) {
		             //do stuff
		             that.ask(q);
		        });

			},
			controllerAs: 'play'
		}
	});

}());