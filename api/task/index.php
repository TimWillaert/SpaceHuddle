<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');

require_once(__DIR__.'/../controllers/task.php');
require_once(__DIR__.'/../controllers/idea.php');
require_once(__DIR__.'/../controllers/group.php');
require_once(__DIR__.'/../controllers/controller.php');

$task = Task_Controller::get_instance();
$idea = Idea_Controller::get_instance();
$group = Group_Controller::get_instance();

if (Controller::is_rest_call("GET")) {
	$result = $task->read();
	echo $result;
}
elseif (Controller::is_rest_call("PUT")) {
	$result = $task->update();
	echo $result;
}
elseif (Controller::is_rest_call("DELETE")) {
	$result = $task->delete();
	echo $result;
}
elseif (Controller::is_rest_call("GET", search_detail_hierarchy: "ideas")) {
	$result = $idea->read_all_from_task();
	echo $result;
}
elseif (Controller::is_rest_call("POST", search_detail_hierarchy: "idea")) {
	$result = $idea->add_to_task();
	echo $result;
}
elseif (Controller::is_rest_call("GET", search_detail_hierarchy: "groups")) {
	$result = $group->read_all_from_task();
	echo $result;
}
elseif (Controller::is_rest_call("POST", search_detail_hierarchy: "group")) {
	$result = $group->add_to_task();
	echo $result;
}
else {
	echo "Call ".$_SERVER["REQUEST_METHOD"]." is not yet implemented";
	http_response_code(501);
}
?>
