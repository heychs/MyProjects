/**
 * Created by Hwansuk Choi <heychs@gmail.com> on 2016-11-16.
 */
'use strict';
angular.module('myApp.controllers', []).
    controller('helloWorldCtrl', function ($scope) {
        $scope.name = { first: "Jane", last: "Doe"};
});