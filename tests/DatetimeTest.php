<?php

namespace Tests\vdeApps\phpCore;

use PHPUnit\Framework\TestCase;
use vdeApps\phpCore\Datetime;

class DatetimeTest extends TestCase
{

    protected $aaaammjj='20180206';
    protected $sqldate='2017-02-06';
    protected $sqldate_samedi='2017-05-27';
    protected $sqldate_14juillet='2018-07-14';
    protected $sqldate_8mai='2018-05-08';
    protected $bisextile='2016-01-03';
    protected $nonbisextile='2017-01-03';
    protected $jjmmaaaa='13/10/2017';
    
    protected $jjmmaaaahhmm='05/09/2018 10:12';
    protected $ts = 1536135120;  //'05/09/2018 10:12'
    
    public function testConstruct(){
        $o = new Datetime();
    
        // Test date du jour
        $this->assertEquals(date('Y-m-d'), $o->format("%Y-%m-%d"));
        
        $o = new Datetime($this->ts);
        $this->assertEquals('2018-09-05 10:12:00.0', $o->toSql());
        
        $o = new Datetime($this->sqldate);
        $this->assertEquals('2017-02-06 00:00:00.0', $o->toSql());
    }
    
    public function testSetDate(){
        
        $o = Datetime::getInstance();
        
        $o->set_date($this->sqldate);
        $this->assertEquals('2017-02-06 00:00:00.0', $o->toSql());
    
        $o->set_date($this->jjmmaaaahhmm);
        $this->assertEquals('2018-09-05 10:12:00.0', $o->toSql());
    
        $o->set_date($this->ts);
        $this->assertEquals('2018-09-05 10:12:00.0', $o->toSql());
    
        $o->set_date($this->bisextile);
        $this->assertEquals(true, $o->est_bissextile());
    
        $o->set_date($this->nonbisextile);
        $this->assertEquals(false, $o->est_bissextile());
        
        
        $o->set_date($this->sqldate);
        $this->assertEquals(2017, $o->get_year());
        $this->assertEquals(2, $o->get_month());
        $this->assertEquals('02', $o->get_month());
        $this->assertEquals(6, $o->get_day());
        $this->assertEquals('06', $o->get_day());
    
        $this->assertEquals(6, $o->get_week());
        $this->assertEquals('06', $o->get_week());
     
        
        //Jour ouvré
        $o->set_date($this->sqldate);
        $this->assertEquals(true, $o->est_jours_ouvres());
        
        $o->set_date($this->sqldate_samedi);
        $this->assertEquals(false, $o->est_jours_ouvres());
    
        $o->set_date($this->sqldate_14juillet);
        $this->assertEquals(false, $o->est_jours_ouvres());
    
        $o->set_date($this->sqldate_8mai);
        $this->assertEquals(false, $o->est_jours_ouvres());
    
        $o->set_date($this->sqldate_8mai);
        $this->assertEquals(128, $o->get_dayofyear());
    
        $this->assertEquals('2018/S19', Datetime::date2sem($o));
    }
}
