<?php
namespace Slime\Component\Log;

/**
 * Class Writer_None
 *
 * @package Slime\Component\Log
 * @author  smallslime@gmail.com
 */
class Writer_None implements IWriter
{
    public function acceptData($aRow)
    {
    }
}