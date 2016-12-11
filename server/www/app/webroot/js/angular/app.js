var uqmarkupApp = angular.module("uqmarkupApp", []);
var baseURL = '/_dev';

var elements = {'userlookup':{}};

uqmarkupApp.directive('userlookup',function() {
	console.log("Starting once");
    return {
   		restrict: "E",
    	templateUrl:baseURL+"/js/angular/views/userlookup.html",
    	controller:"userlookupController",
    	scope: {course:"@"},
   		link: function postLink(scope, iElement, iAttrs) {}
   	}
});

uqmarkupApp.directive('jq:animate', function(jQueryExpression, templateElement){
	console.log(instanceElement);
	return function(instanceElement){
		instanceElement.show('slow');
	}
});