<?php
function pre($object){
	return '<pre>'. print_r($object, true). '</pre>';
}

function pre_die($object){
	die(pre($object));
}

function die_with_error($error){
	die($error);
}