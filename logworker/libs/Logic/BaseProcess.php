<?php
interface BaseProcess {
	public function preProcess();  
	public function runProcess();  
	public function doneProcess();
}
?>
