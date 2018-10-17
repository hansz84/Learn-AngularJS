// define application
angular.module('crudApp', []).controller('userController', function($scope, $http) {
	$scope.users = [];
	$scope.tempUserData = {};
	// function to get records from the database
	$scope.getRecords = function() {
		$http.get('action.php', {
			params: { 
				'type': 'view'
			}
		}).success(function(response) {
			if (response.status == 'OK') {
				$scope.users = response.records;
			}
		});
	};

	// function to insert or update user data to the database
	$scope.saveUser = function(type) {
		var data = $.param({
			'data': $scope.tempUserData,
			'type': type
		});
		var config = {
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
			}
		};
		$http.post("action.php", data, config).success(function(response) {
			if (response.status == 'OK') {
				if (type == 'edit') {
					$scope.users[$scope.index].id = $scope.tempUserData.id;
					$scope.users[$scope.index].name = $scope.tempUserData.name;
					$scope.users[$scope.index].email = $scope.tempUserData.email;
					$scope.users[$scope.index].phone = $scope.tempUserData.phone;
					$scope.users[$scope.index].created = $scope.tempUserData.created;
				} else {
					$scope.users.push({
						id: response.data.id,
						name: response.data.name,
						email: response.data.email,
						phone: response.data.phone,
						created: response.data.created
					});
				}
				$scope.userForm.$setPristine();
				$scope.tempUserData = {};
				$('.formData').slideUp();
				$scope.messageSuccess(response.msg);
			} else {
				$scope.messageError(response.msg);
			}
		});
	};

	// function to add user data
	$scope.addUser = function() {
		$scope.saveUser('add');
	};

	// function to edit user data
	$scope.editUser = function(user) {
		$scope.tempUserData = {
			id: user.id,
			name: user.name,
			email: user.email,
			phone: user.phone,
			created: user.created
		};
		$scope.index = $scope.users.indexOf(user);
		$('.formData').slideDown();
	};

	// function to update user data
	$scope.updateUser = function() {
		$scope.saveUser('edit');
	};

	// function to delete user fata from the database
	$scope.deleteUser = function(user) {
		var conf = confirm('Are you sure to delete the user?');
		if (conf == true) {
			var data = $.param({
				'id': user.id,
				'type': 'delete'
			});
			var config = {
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
				}
			};
			$http.post("action.php", data, config).success(function(response) {
				if (response.status == 'OK') {
					var index = $scope.users.indexOf(user);
					$scope.users.splice(index, 1);
					$scope.messageSuccess(response.msg);
				} else {
					$scope.messageError(response.msg);
				}
			});
		}
	};

	// function to display success message
	$scope.messageSuccess = function(msg) {
		$('.alert-success > p').html(msg);
		$('.alert-success').show();
		$('.alert-success').delay(5000).slideUp(function() {
			$('.alert-success > p').html('');
		});
	};

	// function to display error message
	$scope.messageError = function(msg) {
		$('.alert-danger > p').html(msg);
		$('.alert-danger').show();
		$('.alert-danger').delay(5000).slideUp(function() {
			$('.alert-danger > p').html('');
		});
	};
});