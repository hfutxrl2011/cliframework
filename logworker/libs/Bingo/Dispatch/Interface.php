<?php
/**
 * dispatch接口
 * @author xuliqiang <xuliqiang@baidu.com>
 * @since 2010-02-24
 * @package bingo2.0
 *
 */
interface Bingo_Dispatch_Interface
{
    public function dispatch($strDispatchRouter); 
    
    public function getAction();
}