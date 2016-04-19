/**
 * Main AngularJS Web Application
 */
(function () {
    var app = angular.module('MYLCApp', ['ngRoute', 'ui.bootstrap']);

    /**
     * Configure the Routes
     */
    app.config(configuraton);

    function configuraton($routeProvider) {
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
            .when("/support", {
                templateUrl: "partials/support.html"
            })
            .otherwise({
                redirectTo: "/"
            });
    }

    app.directive('scrollPosition', function($window) {
        return {
            scope: {
                scroll: '=scrollPosition'
            },
            link: function(scope, element, attrs) {
                var windowEl = angular.element($window);
                var handler = function() {
                    scope.scroll = windowEl.scrollTop();
                }
                windowEl.on('scroll', scope.$apply.bind(scope, handler));
                handler();
            }
        };
    });


    /**
     * Controls the page
     */

    app.controller('PageCtrl', pageCtrl);

    function pageCtrl($scope, $location, $http, $sce) {
        $scope.renderHtml = function (html_code) {
            return $sce.trustAsHtml(html_code);
        }

        //show or hide the jumbotron
        $scope.checkURl = function () {
            if ($location.url() == '/' || $location.url().indexOf('/#') > -1) return true;
            return false;
        }

        $scope.$on('$locationChangeStart', function(event) {
            $scope.checkbg = ! $scope.checkURl();
        });

        //accordion
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
                        if(usr.post_actions == "joinacts") $scope.msg = res.msg;
                        if(usr.post_actions == "support") $scope.supmsg = res.msg;
                    } else {
                        alert("You are at Failure");
                        //var ply = new Ply({el: data.error});
                        //ply.open();
                    }
                });

        };

        $scope.scroll = 0;

    }

})();