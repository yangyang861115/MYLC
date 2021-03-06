/**
 * Main AngularJS Web Application
 */
(function () {
    var app = angular.module('MYLCApp', ['ngRoute', 'ui.bootstrap']);

    /**
     * Configure the Routes
     */
    app.config(configuration);

    function configuration($routeProvider) {
        $routeProvider
            .when("/", {
                templateUrl: "partials/home.html"
            })
            .when("/about", {
                templateUrl: "partials/about.html",
            })
            .when("/resources", {
                templateUrl: "partials/resources.html"
            })
            .when("/radio", {
                templateUrl: "partials/radio.html"
            })
            .when("/radio/sdouglass", {
                templateUrl: "partials/sdouglass.html"
            })
            .when("/radio/archives", {
                templateUrl: "partials/archives.html"
            })
            .when("/radio/stations", {
                templateUrl: "partials/stations.html"
            })
            .when("/radio/dailyemail", {
                templateUrl: "partials/dailyemail.html"
            })
            .when("/radio/contactus", {
                templateUrl: "partials/contactus.html"
            })
            .when("/support", {
                templateUrl: "partials/support.html"
            })
            .otherwise({
                redirectTo: "/"
            });
    }

    app.run(function($rootScope, $location, $anchorScroll) {
        //when the route is changed scroll to the proper element.
        $rootScope.$on('$routeChangeSuccess', function(newRoute, oldRoute) {
            if($location.hash()) $anchorScroll();
        });
    });


    app.directive('scrollPosition', function ($window) {
        return {
            scope: {
                scroll: '=scrollPosition'
            },
            link: function (scope, element, attrs) {
                var windowEl = angular.element($window);
                var handler = function () {
                    scope.scroll = windowEl.scrollTop();
                }
                windowEl.on('scroll', scope.$apply.bind(scope, handler));
                handler();
            }
        };
    });

    //app.directive('audioPlayer', AudioPlayer);
    //
    //
    //
    //app.run(function($rootScope, $state, $stateParams, $log){
    //    $rootScope.$state = $state;
    //    $rootScope.$stateParams = $stateParams;
    //    $rootScope.$on('$stateChangeSuccess', function(event, toState, toParams, fromState, fromParams){
    //        try{
    //            createjs.Sound.removeAllSounds();
    //        }catch (err){
    //
    //        }
    //    })
    //});

    /**
     * Controls the page
     */

    app.controller('PageCtrl', pageCtrl);

    function pageCtrl($scope, $location, $http, $sce) {

        //render html code
        $scope.renderHtml = function (html_code) {
            return $sce.trustAsHtml(html_code);
        }

        $scope.trustSrc = function(src) {
            return $sce.trustAsResourceUrl(src);
        }

        //show or hide the jumbotron
        $scope.checkURl = function () {
            if ($location.url() == '/' || $location.url().indexOf('/#') > -1) return true;
            return false;
        }

        $scope.$on('$locationChangeStart', function (event) {
            $scope.checkbg = !$scope.checkURl();
            //navigation highlight
            $scope.location = $location.url();
        });


        //accordion in resource page
        $scope.status = {
            isFirstOpen: true,
            isSecondOpen: false,
            isThirdOpen: false
        };
        //form: learnFm supFm
        $scope.EMAIL_PATTERN = /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/;

        $scope.submitFm = function (usr) {
            $http.post("https://crucore.com/api.php", usr)
                .success(function (res) {
                    if (res.success) {
                        if (usr.post_actions == "joinacts") $scope.msg = res.msg;
                        if (usr.post_actions == "support") $scope.supmsg = res.msg;
                    } else {
                        alert("You are at Failure");
                        //var ply = new Ply({el: data.error});
                        //ply.open();
                    }
                });
        };

        $scope.scroll = 0;

        //broadcast in radio page
        $scope.broadcasts = [];
        if($location.url() == "/radio" && $scope.broadcasts.length == 0){
            $http.get("broadcast.json")
                .success(function (res) {
                    //console.log(res);
                    $scope.broadcasts = res;
                })
        }

    }

})();