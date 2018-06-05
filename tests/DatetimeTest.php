<?php

namespace Tests\vdeApps\phpCore;

use PHPUnit\Framework\TestCase;
use vdeApps\phpCore\Datetime;

class DatetimeTest extends TestCase
{

    public function testConstruct(){
        $ts = time();
        $aaaammjj='2018-10-12';
        $dt = new Datetime();
        print_r($dt->adate);

        $dt = new Datetime($ts);
        print_r($dt->adate);

        $dt = new Datetime($aaaammjj);
        print_r($dt->adate);

    }
}
