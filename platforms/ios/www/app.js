(function() {
	var app = angular.module('dragonasi', ['player-selection', 'waiting-players', 'play', 'connecting']);

	app.controller('DragonasiController', function($scope, $rootScope) {
		$rootScope.step = 0;

        this.restart = function() {
            $rootScope.selectedPlayer = false;
            $rootScope.connectedPlayers = [];
            $rootScope.step = 0;
            $rootScope.$apply();
            window.ws.reconnect();
        };

        $rootScope.$on('restart', this.restart);

		window.ws.init($rootScope, function() {
            $rootScope.step = 1;
            $rootScope.$apply();
			window.ws.send('{"command": "connect"}');
		});

		document.addEventListener("resume", function() {
			window.ws.init();
			$rootScope.selectedPlayer = false;
			$rootScope.connectedPlayers = [];
			$rootScope.step = 1;
		}, false);
		document.addEventListener("pause", function() {
			window.ws.quit();
		}, false);
	});

}());

(function($) {
    $.fn.textfill = function(maxFontSize) {
        maxFontSize = parseInt(maxFontSize, 10);
        return this.each(function(){
            var ourText = $("span", this),
                parent = ourText.parent(),
                maxHeight = parent.height(),
                maxWidth = parent.width(),
                fontSize = parseInt(ourText.css("fontSize"), 10),
                multiplier = maxWidth/ourText.width(),
                newSize = (fontSize*(multiplier-0.1));
            ourText.css(
                "fontSize", 
                (maxFontSize > 0 && newSize > maxFontSize) ? 
                    maxFontSize : 
                    newSize
            );
        });
    };
})(jQuery);