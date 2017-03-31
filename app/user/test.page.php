<?php
class user_test{
	public function pageEntry($inPath){
		$result = user_db_studentDao::test();
		print_R($result);die;
	}
}
