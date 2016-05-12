'use strict';

//function AudioPlayer ($log, $timeout, $window, $rootScope) {
//    return {
//        restrict: "A",
//        template: '<div class="transportControl"><div ng-click="toggleAudio()" ng-class="{\'play\': !state.playing, \'pause\': state.playing,\'loading\': state.loading,  \'fa-spin\': state.loading}" class="icon"></div></div>',
//        replace: true,
//        scope: {
//            song: '@'
//        },
//        link: function($scope, $element, $attr) {
//            $scope.state = {};
//            var audioPath = "/audio/";
//            var track;
//            function init(){
//                $log.debug("AudioPlayer: Init");
//            }
//
//            $scope.toggleAudio = function(){
//                $log.debug("AudioPlayer: Toggling");
//                if(!$scope.state.playing) {
//                    playAudio();
//                } else {
//                    pauseAudio();
//                }
//            };
//
//            $scope.on("stopAllSounds", function(t) {
//                $log.debug("AudioPlayer: " + $scope.song + " stopping");
//                if(t != $scope.song && typeof(track) != 'undefined'){
//                    return pauseAudio();
//                }
//            });
//
//            function playAudio(){
//                $log.debug("AudioPlayer: Playing");
//                $rootScope.$broadcast('stopAllSounds', $scope.song);
//                if(typeof(track) != 'undefined') {
//                    $scope.state.loading = false;
//                    $scope.state.playing = true;
//                    track.setPositon(0);
//                    track.play($scope.song);
//                    $log.debug("AudioPlayer: File loaded so playing track", track);
//                } else {
//                    $log.debug("AudioPlayer: File not loaded to play so will create it");
//                    $scope.state.loading = true;
//                    $scope.state.playing = false;
//                    createjs.Sound.removeAllEventListeners("fileload");
//                    createjs.Sound.registerSound($scope.song + ".mp3", $scope.song, 1, true, audioPath);
//                    createjs.Sound.addEventListener("fileload", handleLoaded);
//                }
//            }
//
//            function handleLoaded(){
//                $log.debug("AudioPlayer: File  loaded " + $scope.song);
//                track = createjs.Sound.createInstance($scope.song);
//                $scope.state.loading = false;
//                $scope.state.playing = true;
//                track.play();
//                $scope.$apply();
//            }
//
//            function pauseAudio(){
//                if(track) {
//                    $log.debug("AudioPlayer: Pausing for song "+ $scope.song);
//                    track.pause();
//                    $scope.state.loading = false;
//                    $scope.state.playing = false;
//                }
//            }
//
//            init();
//        }
//
//    }
//}
