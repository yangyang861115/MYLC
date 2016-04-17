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
                redictTo: "/"
            });
    }
    /**
     * Controls the page
     */

    app.controller('PageCtrl', pageCtrl);

    function pageCtrl($scope, $location){

        //show or hide the jumbotron
        $scope.checkURl = function() {
            if($location.url() == '/' || $location.url().indexOf('/#') > -1) return true;
            return false;
        }

        //accordion
        $scope.status = {
            isFirstOpen: true,
            isSecondOpen: false,
            isThirdOpen: false
        };

        $scope.load = function() {
            // do your $() stuff here

            $('#mainNav').affix({
                offset: {
                    top: 100
                }
            });

            $('#formvals').formValidation({
                framework: 'bootstrap',
                icon: {
                    valid: 'glyphicon glyphicon-ok',
                    invalid: 'glyphicon glyphicon-remove',
                    validating: 'glyphicon glyphicon-refresh'
                },
                fields: {
                    nameline: {
                        verbose:false,
                        icon: false,
                        trigger: 'blur',
                        validators: {
                            notEmpty: {
                                message: 'Your name is required'
                            }
                        }
                    },
                    email: {
                        verbose:false,
                        icon: false,
                        trigger: 'blur',
                        validators: {
                            notEmpty: {
                                message: 'The email is required and cannot be empty'
                            },
                            emailAddress: {
                                message: 'The value is not a standard email address'
                            },
                            regexp: {
                                regexp: '^[^@\\s]+@([^@\\s]+\\.)+[^@\\s]+$',
                                message: 'The value is not a valid email address'
                            }
                        }
                    }
                }
            });
            $('#frmsbmt').on("click",function(e){
                $("#formvals").data('formValidation').validate();
                if(!$("#formvals").data('formValidation').isValid()) {
                    return false; }
                e = e || window.event;
                e.preventDefault();
                $.ajax({
                    url:"https://crucore.com/api.php",
                    data:$("#formvals").serialize(),
                    dataType:"JSON",
                    type:"POST",
                    success: function(data){
                        if(data.success) {
                            var msg = data.msg;
                            $("#showresp").html(msg);
                            // window.location="http://essentials24.org";
                        }
                        else {
                            alert("You are at Failure");
                            var ply = new Ply({el:data.error});
                            ply.open();
                        }
                    }
                });
            });

        };

        //don't forget to call the load function
        $scope.load();


    }

})();



//
///**
// * Controls all other Pages
// */
//app.controller('PageCtrl', function ( /* $scope, $location, $http */ ) {
//    console.log("Page Controller reporting for duty.");
//
//    // Activates the Carousel
//    $('.carousel').carousel({
//        interval: 5000
//    });
//
//    // Activates Tooltips for Social Links
//    $('.tooltip-social').tooltip({
//        selector: "a[data-toggle=tooltip]"
//    })
//});