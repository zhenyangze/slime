<?php
namespace Slime\Component\Log;

/**
 * Interface IWriter
 *
 * @package Slime\Component\Log
 * @author  smallslime@gmail.com
 */
interface IWriter
{
    public function acceptData($aRow);
}