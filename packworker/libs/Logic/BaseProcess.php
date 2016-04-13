<?php
interface BaseProcess {
	public function downloadIMG(&$data);  
	public function execShell(&$data);  
	public function moveFiles(&$data);
}
?>
