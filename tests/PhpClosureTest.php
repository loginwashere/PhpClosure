<?php
namespace PhpClosure\Test;

use PhpClosure\PhpClosure;

class PhpClosureTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $phpClosure = PhpClosure::create();
        $this->assertInstanceOf('PhpClosure\PhpClosure', $phpClosure);
    }
}