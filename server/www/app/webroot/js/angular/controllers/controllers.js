function TutorLookupController($scope, userLookupService) {
    'use strict';
    
    $scope.tutors = [];
    
    $scope.searchFilter = 'markers';
    $scope.setSearchFilter = function(type) {
        $scope.searchFilter = type;
    };
    
    $scope.doSearch = function() {
        $scope.users = [];
        if($scope.searchKeyword.length > 3) {
        	  userLookupService.findUsers($scope.searchKeyword, $scope.searchFilter, function(tutors) {
      	  	  $scope.users = tutors;
      	  });
        }
    };
}