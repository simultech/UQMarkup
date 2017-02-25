function userlookupController($scope, userLookupService) {
    'use strict';

    $scope.tutors = [];

    $scope.searchFilter = 'students';
    $scope.setSearchFilter = function(type) {
        $scope.searchFilter = type;
        $scope.doSearch();
    };

    $scope.doSearch = function() {
        $scope.users = [];
        if($scope.searchKeyword.length > 3) {
            userLookupService.findUsers($scope.searchKeyword, $scope.searchFilter, $scope.course, function(tutors) {
                $scope.users = tutors;
            });
        }
    };

    $scope.doCopyUser = function () {
        //alert("WTF");
        //clip.setText( "Copy me!" );
        //console.log(clip);
    }
}