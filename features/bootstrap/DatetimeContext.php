<?php
/**
 * Copyright AUXITEC TECHNOLOGIES (groupe Artélia)
 */

//namespace Tests\Bdd;

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

/**
 * Defines application features from the specific context.
 */
class DatetimeContext implements Context
{
    protected $dt;
    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        $this->dt = new \vdeApps\phpCore\Datetime();
    }
    
    

    /**
     * @Given La date est :aaaammjj
     */
    public function laDateEst($aaaammjj)
    {
        $this->dt = new \vdeApps\phpCore\Datetime($aaaammjj);
    
        if ( $aaaammjj !== $this->dt->format("%Y-%m-%d") )
            throw new \Exception("Erreur d'initialisation de la date $aaaammjj");
        
    }

    /**
     * @Then Jour pas ferie
     */
    public function jourPasFerie()
    {
        $aaaammjj = $this->dt->format('%Y-%m-%d');
        if ($this->dt->EstFerie() === true ){
            throw new \Exception("La date $aaaammjj est férié");
        }
    }

    /**
     * @Then Le jour est ferie
     */
    public function leJourEstFerie()
    {
        $aaaammjj = $this->dt->format('%Y-%m-%d');
        if ($this->dt->EstFerie() === false ){
            throw new \Exception("La date $aaaammjj n'est pas férié");
        }
//        var_export($this->dt->getListeJoursFeries());
    }

    /**
     * @Given On ajoute un jour
     */
    public function onAjouteUnJour()
    {
        $aaaammjj_before_add = $this->dt->format("%Y%m%d");
        $this->dt->add_days(1);
        $aaaammjj_after_add = $this->dt->format("%Y%m%d");
    }
}
