/**
 * Created by Hwansuk Choi <heychs@gmail.com> on 2016-11-16.
 */
'use strict';
angular.module('myApp.directives', [])
    .directive('helloWorld', function () {
        return {
            restrict: 'AE',
            scope: { name: "=name" },
            template:
            "<h1>Hello {{ name.first }} {{ name.last }}!</h1>" +
            "<input data-ng-model='name.first'/>" +
            "<input data-ng-model='name.last'/>"
        }
    });