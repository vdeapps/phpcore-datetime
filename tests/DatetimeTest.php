<?php

namespace Tests\vdeApps\phpCore;

use PHPUnit\Framework\TestCase;
use vdeApps\phpCore\Datetime;

class DatetimeTest extends TestCase
{
    protected $ts = 0;
    protected $sqldate = '2018-09-05';
    protected $sqldatetime = '2018-09-05 10:12:30';
    protected $strdatetime = '5/9/2018 10:12:30';

    protected $sqldate_samedi = '2017-05-27';
    protected $sqldate_14juillet = '2018-07-14';
    protected $sqldate_8mai = '2018-05-08';
    protected $bisextile = '2016-01-03';
    protected $nonbisextile = '2017-01-03';

    /** @var Datetime $dt */
    private $dt = null;

    /*
     * Before each test
     */
    protected function setUp()
    {
        $this->dt = new Datetime();
    }

    public function testExistsClass()
    {
        $this->assertEquals('vdeApps\phpCore\Datetime', Datetime::class);
    }

    public function testCompareAAAAMMJJ()
    {
        $this->dt->set_date($this->sqldate);
        $this->assertEquals('20180905', $this->dt->format('%Y%m%d'));
    }
    public function testCompareJJMMAAAA()
    {
        $this->dt->set_date('05/09/2018');
        $this->assertEquals('20180905', $this->dt->format('%Y%m%d'));

        $this->assertEquals('2018-09-05 00:00:00.0', $this->dt->toSql());
    }

    public function testCompareJMAAAA()
    {
        $this->dt->set_date('5/9/2018');
        $this->assertEquals('20180905', $this->dt->format('%Y%m%d'));
    }

    public function testSetdateFormatFromAAAAMMJJHHMMSS()
    {
        $this->dt->set_date($this->sqldatetime);

        $this->assertEquals('20180905', $this->dt->format('%Y%m%d'));

        $this->assertEquals('2018-09-05', $this->dt->format('%Y-%m-%d'));

        $this->assertEquals('10:12', $this->dt->format('%H:%M'));
    }

    public function testSetdateFormatFromJJMMAAAAHHMMSS()
    {
        $this->dt->set_date($this->strdatetime);

        $this->assertEquals('20180905', $this->dt->format('%Y%m%d'));

        $this->assertEquals('2018-09-05', $this->dt->format('%Y-%m-%d'));

        $this->assertEquals('10:12', $this->dt->format('%H:%M'));
    }

    public function testSetdateFormatFromJMAAAAHHMMSS()
    {
        $this->dt->set_date('5/9/2018 10:12:30');

        $this->assertEquals('20180905', $this->dt->format('%Y%m%d'));

        $this->assertEquals('2018-09-05', $this->dt->format('%Y-%m-%d'));

        $this->assertEquals('10:12', $this->dt->format('%H:%M'));

        $this->assertEquals('2018-09-05 10:12:30.0', $this->dt->toSql());
    }

    public function testEstBisextile()
    {
        $this->dt->set_date('2016-01-03');
        $this->assertEquals(true, $this->dt->est_bissextile());
    }

    public function testEstNonBisextile()
    {
        $this->dt->set_date('2017-01-03');
        $this->assertEquals(false, $this->dt->est_bissextile());
    }

    public function testOnebyOne()
    {
        $this->dt->set_date($this->sqldatetime);

        $this->assertEquals(2018, $this->dt->get_year());
        $this->assertEquals(9, $this->dt->get_month());
        $this->assertEquals('09', $this->dt->get_month());
        $this->assertEquals(5, $this->dt->get_day());
        $this->assertEquals('05', $this->dt->get_day());

        $this->assertEquals(36, $this->dt->get_week());
        $this->assertEquals('36', $this->dt->get_week());
    }

    public function testSemaine()
    {
        $this->assertEquals('2018/S36', Datetime::date2sem($this->strdatetime));
    }

    public function testJoursOuvres()
    {
        $this->dt->set_date($this->sqldatetime);
        $this->assertEquals(true, $this->dt->est_jours_ouvres());

        $this->dt->set_date($this->sqldate_samedi);
        $this->assertEquals(false, $this->dt->est_jours_ouvres());

        $this->dt->set_date($this->sqldate_14juillet);
        $this->assertEquals(false, $this->dt->est_jours_ouvres());

        $this->dt->set_date($this->sqldate_8mai);
        $this->assertEquals(false, $this->dt->est_jours_ouvres());

        $this->dt->set_date($this->sqldate_8mai);
        $this->assertEquals(128, $this->dt->get_dayofyear());
    }



    public function testAdd5days()
    {
        $this->dt->set_date($this->sqldatetime);
        $this->dt->add_days(5);

        $this->assertEquals('2018-09-10', $this->dt->format('%Y-%m-%d'));
    }

    public function testAdd28days()
    {
        $this->dt->set_date($this->sqldatetime);
        $this->dt->add_days(28);

        $this->assertEquals('2018-10-03', $this->dt->format('%Y-%m-%d'));
    }

    public function testSub5days()
    {
        $this->dt->set_date($this->sqldatetime);
        $this->dt->sub_days(5);

        $this->assertEquals('2018-08-31', $this->dt->format('%Y-%m-%d'));
    }

    public function testAdd28daysOuvres()
    {
        $this->dt->set_date($this->sqldatetime);
        $this->dt->set_jours_ouvres(true);
        $this->dt->add_days(28);

        $this->assertEquals('2018-10-15', $this->dt->format('%Y-%m-%d'));
    }

    public function testGetPeriode()
    {
        $result = Datetime::getPeriode('2018-09-05','10/09/2019');
        $this->assertEquals(['lib','shortlib','start','end'], array_keys($result));
    }

    public function testJoursFeries(){
        $this->dt->set_date('2018-12-25');
        $this->assertTrue($this->dt->EstFerie());

        $this->dt->set_date('2018-12-24');
        $this->assertFalse($this->dt->EstFerie());
    }
    
    /**
     * Test les jours fériés
     * @throws \Exception
     */
    public function testPaques(){
        $this->dt->set_date("2019-04-22");
        $this->assertTrue($this->dt->EstFerie());

        $this->dt->set_date('2020-04-12');
        $this->assertFalse($this->dt->EstFerie());

        $this->dt->set_date('2020-04-13');
        $this->assertTrue($this->dt->EstFerie());

        $this->dt->set_date('2021-04-05');
        $this->assertTrue($this->dt->EstFerie());
    }
}
