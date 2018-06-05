<?php

namespace vdeApps\phpCore;

class Datetime
{
    const REGEX_SQL = "/(?<fulldate>(?<Y>\d{4})(-)*(?<M>\d{2})(-)*(?<D>\d{2}))(( ){1}(?<fulltime>(?<h>\d{2}):(?<m>\d{2})(:(?<s>\d{2})){0,1}(:(\d{2})){0,1})){0,1}/";
    const REGEX_STR = "/(?<fulldate>(?<D>\d{2})(\/)*(?<M>\d{2})(\/)*(?<Y>\d{4}))(( ){1}(?<fulltime>(?<h>\d{2}):(?<m>\d{2})(:(?<s>\d{2})){0,1}(:(\d{2})){0,1})){0,1}/";
    static $longday = [
        'Monday'    => 'Lundi',
        'Tuesday'   => 'Mardi',
        'Wednesday' => 'Mercredi',
        'Thursday'  => 'Jeudi',
        'Friday'    => 'Vendredi',
        'Saturday'  => 'Samedi',
        'Sunday'    => 'Dimanche',
    ];
    static $shortday = [
        'Monday'    => 'Lun',
        'Tuesday'   => 'Mar',
        'Wednesday' => 'Mer',
        'Thursday'  => 'Jeu',
        'Friday'    => 'Ven',
        'Saturday'  => 'Sam',
        'Sunday'    => 'Dim',
    ];
    
    /*
     * Equivalent en secondes
     * w: semaine
     * d: jour
     * h: heure
     * m: minute
     * s: seconde
     */
    static $TIME2SEC = ['w' => 604800, 'd' => 86400, 'h' => 3600, 'm' => 60, 's' => 1];
    static $array_mois = [1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'];
    static $array_moismini = [1 => 'JAN', 2 => 'FEV', 3 => 'MAR', 4 => 'AVR', 5 => 'MAI', 6 => 'JUN', 7 => 'JUI', 8 => 'AOU', 9 => 'SEP', 10 => 'OCT', 11 => 'NOV', 12 => 'DEC'];
    var $time = null;
    var $adate = null;
    
    
    var $joursferies = null;
    var $ts_easter = null;
    var $flag_jours_ouvres = false;
    
    /**
     * Constructeur
     *
     * @param int $timestamp (default: time())
     */
    public function __construct($timestamp = null)
    {
        if (is_numeric($timestamp)) {
            $this->set_timestamp($timestamp);
        } elseif (is_string($timestamp)) {
            $this->set_date($timestamp);
        } else {
            $this->set_timestamp(time());
        }
    }
    
    /**
     * Met � jour le timestamp
     *
     * @param int $ts
     *
     * @return awcDatetime
     */
    public function set_timestamp($ts = null)
    {
        if (!is_numeric($ts)) {
            $this->time = null;
            $this->adate = null;
            
            return false;
        }
        
        $old_year = ($this->time == null) ? false : $this->adate['year'];
        
        $this->time = $ts;
        $this->adate = getdate($ts);
        
        $this->adate['daysInMonth'] = date('t', $ts);
        
        //Test si on doit recalculer les jours fériés
        if ($old_year === false || $old_year != $this->adate['year']) {
            $this->set_joursferies($this->adate['year']);
        }
        
        return $this;
    }
    
    /*
     * SETTER
     */

    /**
     * Recalcul les jours f�ri�s de l'ann�e cDateTime
     *
     * @param int $year
     *
     * @return awcDatetime
     */
    public function set_joursferies($year)
    {
        $this->joursferies = null;
        $this->joursferies["${year}0101"] = true;
        $this->joursferies["${year}0501"] = true;
        $this->joursferies["${year}0508"] = true;
        $this->joursferies["${year}0714"] = true;
        $this->joursferies["${year}0815"] = true;
        $this->joursferies["${year}1101"] = true;
        $this->joursferies["${year}1111"] = true;
        $this->joursferies["${year}1225"] = true;
        
        //Timestamp du dimanche de paques
        $this->ts_easter = self::get_easter_date($year);
        
        //Lundi de paques
        $ts_feriers = $this->ts_easter + 1 * self::$TIME2SEC['d'];
        $aaaammdd_ferie = date('Ymd', $ts_feriers);
        $this->joursferies["$aaaammdd_ferie"] = true;
        
        //Jeudi de l'Ascension
        $ts_feriers = $this->ts_easter + 39 * self::$TIME2SEC['d'];
        $aaaammdd_ferie = date('Ymd', $ts_feriers);
        $this->joursferies["$aaaammdd_ferie"] = true;
        
        //Lundi de Pentec�te
        $ts_feriers = $this->ts_easter + 50 * self::$TIME2SEC['d'];
        $aaaammdd_ferie = date('Ymd', $ts_feriers);
        $this->joursferies["$aaaammdd_ferie"] = true;
        
        return $this;
    }
    
    /**
     * Retourne du lundi de paque
     *
     * @param int $year
     *
     * @return timestamp
     */
    public static function get_easter_date($year = null)
    {
        if ($year == null) {
            $year = date('Y');
        }
        $ts_easter = easter_date($year);
        if ($ts_easter === false) {
            return false;
        }
        
        return $ts_easter;
    }
    
    /**
     * Initialise l'objet avec une date au format string
     *
     * @param string|int|null $date (format: YYYY-MM-DD[ h:m:s] ou YYYYMMDD ou timestamp ou current time si date=null)
     *
     * @return awcDatetime
     */
    public function set_date($date = null)
    {
        if (is_null($date)) {
            $this->set_timestamp(time());
            
            return $this;
        } elseif (is_numeric($date)) {
            $this->set_timestamp($date);
            
            return $this;
        } elseif (preg_match(self::REGEX_SQL, $date, $dateSplitted)) {
            if (\checkdate($dateSplitted['M'], $dateSplitted['D'], $dateSplitted['Y'])) {
                $h = awcIs::PARAM($dateSplitted, 'h', "is_numeric", 0);
                $m = awcIs::PARAM($dateSplitted, 'm', "is_numeric", 0);
                $s = awcIs::PARAM($dateSplitted, 's', "is_numeric", 0);
                
                $mkt = mktime($h, $m, $s, $dateSplitted['M'], $dateSplitted['D'], $dateSplitted['Y']);
                $this->set_timestamp($mkt);
                
                return $this;
            } else {
                return false;
            }
        } elseif (preg_match(self::REGEX_STR, $date, $dateSplitted)) {
            if (\checkdate($dateSplitted['M'], $dateSplitted['D'], $dateSplitted['Y'])) {
                $h = awcIs::PARAM($dateSplitted, 'h', "is_numeric", 0);
                $m = awcIs::PARAM($dateSplitted, 'm', "is_numeric", 0);
                $s = awcIs::PARAM($dateSplitted, 's', "is_numeric", 0);
                
                $mkt = mktime($h, $m, $s, $dateSplitted['M'], $dateSplitted['D'], $dateSplitted['Y']);
                $this->set_timestamp($mkt);
                
                return $this;
            } else {
                return false;
            }
        }
        
        return false;
    }
    
    static function getInstance($timestamp = null)
    {
        
        return new self($timestamp);
    }
    
    /*
     * CALCUL
     */
    
    /**
     * Retourne l'ann�e et mois format�s
     *
     * @param integer $mmaaaa
     * @param string  $format
     * @param string  $separator default ' '
     *
     * @return string
     */
    public static function getmoisannee($mmaaaa, $format = 'mm/aaaa', $separator = ' ')
    {
        $str = '';
        switch ($format) {
            case 'aaaamm':
                $str = self::getmois(substr($mmaaaa, 4, 2)) . $separator . substr($mmaaaa, 0, 4);
                break;
            case 'mmaaaa':
                $str = self::getmois(substr($mmaaaa, 0, 2)) . $separator . substr($mmaaaa, 2);
                break;
            case 'mm/aaaa':
                $date = explode('/', $mmaaaa);
                if ($date) {
                    $str = self::getmois($date[0]) . $separator . $date[1];
                }
                break;
        }
        
        return $str;
    }
    
    public static function getmois($val)
    {
        if (is_numeric($val)) {
            return self::$array_mois[intval($val)];
        }
        
        return false;
    }
    
    /**
     * Retourne l'ann�e et mois format�s
     *
     * @param integer $mmaaaa
     * @param string  $format
     * @param string  $separator default ' '
     *
     * @return string
     */
    public static function getmoisanneemini($mmaaaa, $format = 'mm/aaaa', $separator = ' ')
    {
        $str = '';
        switch ($format) {
            case 'aaaamm':
                $str = self::getmoismini(substr($mmaaaa, 4, 2)) . $separator . substr($mmaaaa, 0, 4);
                break;
            case 'mmaaaa':
                $str = self::getmoismini(substr($mmaaaa, 0, 2)) . $separator . substr($mmaaaa, 2);
                break;
            case 'mm/aaaa':
                $date = explode('/', $mmaaaa);
                if ($date) {
                    $str = self::getmoismini($date[0]) . $separator . $date[1];
                }
                break;
        }
        
        return $str;
    }
    
    public static function getmoismini($val)
    {
        if (is_numeric($val)) {
            return self::$array_moismini[intval($val)];
        }
        
        return false;
    }
    
    public static function aaaamm()
    {
        return date('Ym');
    }
    
    public static function aaaamm_prev()
    {
        return date('Ym', mktime(0, 0, 0, date('m'), 0, date('Y')));
    }
    
    /**
     * Retourne un tableau contenant la liste de aaaamm jusqu'� aaaamm-nb_mois
     *
     * @param integer $aaaamm
     * @param number  $nb_mois default 12
     *
     * @return array
     */
    public static function getListeAAAAMM_glissants($aaaamm = null, $nb_mois = 12)
    {
        $aperiode = [];
        if ($aaaamm == null) {
            $aaaamm = date('Ym');
        }
        
        $iter = 0;
        $liste = '';
        while ($iter < $nb_mois) {
            $aperiode[] = $aaaamm;
            
            if ($iter > 0) {
                $liste .= ',' . $aaaamm;
            } else {
                $liste .= $aaaamm;
            }
            
            if ($aaaamm % 100 == 1) {
                $aaaamm -= 89;
            } else {
                $aaaamm -= 1;
            }
            
            $iter++;
        }
        
        return array_reverse($aperiode);
    }
    
    public static function options_annee($selected = null, $start = _AAAA_, $end = _AAAA_, $firstRow = ['0' => ''])
    {
        $htmlListe = new awcHtmlListe();
        $anneesListe = [];
        if ($end < $start) {
            for ($annee = $start; $annee >= $end; $annee--) {
                $anneesListe[$annee] = $annee;
            }
        } else {
            for ($annee = $start; $annee <= $end; $annee++) {
                $anneesListe[$annee] = $annee;
            }
        }
        $htmlListe->setbyArray($anneesListe);
        
        if (!empty($firstRow)) {
            $htmlListe->unshift($firstRow);
        }
        
        return $htmlListe->html_option($selected);
    }
    
    /*
     * TESTS
     */
    
    public static function options_mois($selected = null, $firstRow = ['0' => ''])
    {
        $htmlListe = new awcHtmlListe();
        $htmlListe->setbyArray(self::$array_mois);
        
        if (!empty($firstRow)) {
            $htmlListe->unshift($firstRow);
        }
        
        return $htmlListe->html_option($selected);
    }
    
    /**
     * Calcule le nombre de jours entre 2 dates
     *
     * @param numeric $startDate   Date de d�but au format timestamp
     * @param numeric $endDate     Date de fin au format timestamp
     * @param boolean $joursOuvres D�finit si jours ouvr�s ou calendaires
     *
     * @return le nombre de jours entre les 2 dates ou FALSE si une des 2 dates n'est pas un timestamp
     */
    public static function getNbJours($startDate = null, $endDate = null, $joursOuvres = true)
    {
        $nbJours = false;
        if (awcIs::numeric($startDate) && awcIs::numeric($endDate)) {
            $start = new awcDatetime($startDate);
            $start->set_jours_ouvres($joursOuvres);
            $end = new awcDatetime($endDate);
            $end->set_jours_ouvres($joursOuvres);
            $nbJours = ($joursOuvres) ? $start->diff($end)->jo : $start->diff($end)->j;
        }
        
        return $nbJours;
    }
    
    /**
     * Retourne l'ann�e et semaine depuis une date
     *
     * @param string $date format: YYYY-MM-DD, DD/MM/YYYY, YYYY/S01
     *
     * @return string YYYY/S01
     */
    public static function date2sem($date)
    {
        if ($date == '') {
            return false;
        }
        
        return date("o/\SW", strtotime(awcDatetime::date2sql($date)));
    }
    
    /**
     * Retourne une date jj/mm/aaaa ou 2015/S01 au format aaaa-mm-jj
     *
     * @param unknown_type $date
     *
     * @return string
     */
    public static function date2sql($date)
    {
        $dt = new self();
        
        return ($dt->set_date($date)) ? $dt->toSql() : false;
    }
    
    /*
     * GETTER
     */
    
    /**
     * Retourne la date au format SQL
     * @return false|string
     */
    public function toSql()
    {
        return $this->format('%Y-%m-%d %H:%M:%S.0');
    }
    
    /**
     * Converti une date sql au format /
     *
     * @param        $date
     *
     * @param string $format
     *
     * @return string
     */
    public static function datetime2str($date, $format = '%d/%m/%Y à %kh%M')
    {
        return self::date2str($date, $format);
    }
    
    /**
     * Converti une date au format /
     *
     * @param        $date
     *
     * @param string $format
     *
     * @return string
     */
    public static function date2str($date, $format = '%d/%m/%Y')
    {
        $dt = new self();
        if (!$dt->set_date($date)) {
            return '';
        }
        
        return $dt->format($format);
    }
    
    /**
     * Retourne une date jj/mm/aaaa hh:ii au format aaaa-mm-jj hh:ii
     *
     * @param $datetime
     *
     * @return string
     *
     */
    public static function datetime2sql($datetime)
    {
        $datetime = trim($datetime);
        if (empty($datetime)) {
            return false;
        }
        
        // Test si déjà au format aaaa-mm-jj
        if (preg_match('#\d{4}-\d{2}-\d{2} \d{2}:\d{2}#', $datetime)) {
            return $datetime;
        }
        
        return preg_replace('#(\d{1,2})/(\d{1,2})/(\d{1,4}) (\d{1,2}):(\d{1,2})#', "$3-$2-$1 $4:$5:00.0", $datetime);
    }
    
    /**
     * retourne un datetime SQL au format jj/mm/aaaa hh:mm
     *
     * @param string $datetime
     *
     * @return string
     */
    public static function sql2datetime($datetime = null, $separator = ' à ')
    {
        return self::sql2date($datetime, $format = '%d/%m/%Y' . $separator . '%kh%M');
    }
    
    /**
     * Converti une date au format str
     *
     * @param $date
     *
     * @return string|null
     */
    public static function sql2date($date, $format = '%d/%m/%Y')
    {
        if (empty($date)) {
            return null;
        }
        $dt = new self();
        $dt->set_date($date);
        
        return $dt->format($format);
    }
    
    /**
     * Retourne si une date au format chaine est valide
     *
     * @param string $date   (YYYY/MM/DD | YY-MM-DD | YYYYMMDD ...)
     * @param string $format (default:'DD/MM/YYYY')
     *
     * @return timestamp
     */
    public static function valid_date($date = null, $format = 'DD/MM/YYYY')
    {
        if ($date == null) {
            return false;
        }
        $lendate = strlen($date);
        if ($lendate > 10) {
            $date = substr($date, 0, 10);
            $lendate = strlen($date);
        }
        
        if ($lendate >= 8 && $lendate <= 10) {
            $regexp = $format;
            $regexp = str_replace('DD', '(?<D>\d{2})', $regexp);
            $regexp = str_replace('MM', '(?<M>\d{2})', $regexp);
            $regexp = str_replace('YYYY', '(?<Y>\d{4})', $regexp);
            
            if (preg_match("/$regexp/", $date, $aDate)) {
                $year = ($aDate['Y'] < 100) ? 2000 + $aDate['Y'] : $aDate['Y'];
                $month = $aDate['M'];
                $day = $aDate['D'];
                
                if (self::checkdate($month, $day, $year)) {
                    return mktime(0, 0, 0, $month, $day, $year);
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }
    
    /**
     * Retourne si une date est valide ou non
     *
     * @param int $month
     * @param int $day
     * @param int $year
     *
     * @return boolean
     */
    public static function checkdate($month, $day, $year)
    {
        return checkdate($month, $day, $year);
    }
    
    /**
     * Retourne le delai entre 2 date en jours
     *
     * @param cDateTime $objcDateTime
     *
     * @return array(j, jo)
     */
    public function diff($objcDateTime = null)
    {
        if ($this->time == null || $objcDateTime == null || $objcDateTime->time == null) {
            return false;
        }
        
        /*
         * On recopie l'objet
        */
        $d = clone $this;
        $objcDateTime_clone = clone $objcDateTime;
        
        // Test si on calcul les jours ouvr�s
        $flag_jours_ouvres = ($d->flag_jours_ouvres | $objcDateTime_clone->flag_jours_ouvres) ? true : false;
        
        $objdiff = (object)[];
        $objdiff->j = 0;
        $objdiff->jo = 0;
        $signe = 1; //Delai positif ou negatif
        
        //Si la date est la m�me
        $equal_date = $d->equals($objcDateTime_clone);
        if ($equal_date == 0) {
            $objdiff->j = 1;
            
            // Jour ouvr�
            if ($flag_jours_ouvres && $objcDateTime_clone->est_jours_ouvres()) {
                $objdiff->jo = 1;
            }
            
            return $objdiff;
        }
        
        /*
         * Inverse les dates si elles ne sont pas ordonn�es
         */
        $d_start = $d_end = null;
        if ($equal_date < 0) {
            $d_start = $d;
            $d_end = $objcDateTime_clone;
        } else {
            $signe = -1;
            $d_start = $objcDateTime_clone;
            $d_end = $d;
        }
        
        //On retire le flag jours ouvr�es pour que le add_days ne saute pas de jours
        $d_start->set_jours_ouvres(false);
        $d_end->set_jours_ouvres(false);
        
        //      echo '-----<br>';
        while ($d_start->equals($d_end) <= 0) {
            //          echo $d_start->format().' - '.$d_end->format().'<br>';
            
            $objdiff->j++;
            if ($flag_jours_ouvres && $d_start->est_jours_ouvres()) {
                $objdiff->jo++;
            }
            
            $d_start->add_days(1);
            
            //          ob_end_flush();
            //          flush();
        }
        //      echo '-----<br>';
        
        $objdiff->j *= $signe;
        $objdiff->jo *= $signe;
        
        return $objdiff;
    }
    
    /**
     * Test si 2 cDateTime sont identiques
     *
     * @param unknown_type $objcDateTime
     *
     * @return boolean
     */
    public function equals($objcDateTime = null)
    {
        if ($objcDateTime == null) {
            return false;
        }
        
        $diff_year = $this->adate['year'] - $objcDateTime->adate['year'];
        $diff_mon = $this->adate['mon'] - $objcDateTime->adate['mon'];
        $diff_day = $this->adate['mday'] - $objcDateTime->adate['mday'];
        
        if ($diff_year > 0) {
            return 1;
        } elseif ($diff_year < 0) {
            return -1;
        } elseif ($diff_mon > 0) {
            return 1;
        } elseif ($diff_mon < 0) {
            return -1;
        } elseif ($diff_day > 0) {
            return 1;
        } elseif ($diff_day < 0) {
            return -1;
        } else {
            return 0;
        }
    }
    
    /**
     * Set Flag pour le calcul sur jours ouvr�s
     *
     * @param boolean $boolean
     *
     * @return $boolean
     */
    public function set_jours_ouvres($boolean = true)
    {
        $this->flag_jours_ouvres = $boolean;
        
        return $this;
    }
    
    /**
     * Test si la date est un jour ouvr�
     * @return boolean
     */
    public function est_jours_ouvres()
    {
        $aaaammdd = $this->format('Ymd');
        $dayofweek = $this->adate['wday'];
        
        //echo "JO($aaaammdd;$dayofweek;". (isset($this->joursferies["$aaaammdd"])?'jf':'nf') . ')<br/>';
        
        //Samedi et Dimanche
        if ($dayofweek == 0 || $dayofweek == 6 || isset($this->joursferies["$aaaammdd"])) {
            //          echo "JO($aaaammdd;$dayofweek;". (isset($this->joursferies["$aaaammdd"])?'jf':'nf') . ')<br/>';
            return false;
        } else {
            //          echo "JO($aaaammdd;Semaine)<br/>";
        }
        
        return true;
    }
    
    /**
     * Retourne la date suivant le $format
     *
     * @param string $format (default:'DD/MM/YYYY')
     *
     * @return string | timestamp
     */
    public function format($format = '%d/%m/%Y')
    {
        if ($this->time == null) {
            return false;
        }
        
        return strftime($format, $this->time);
    }
    
    /**
     * Ajoute nb jours
     *
     * @param int $days
     *
     * @return awcDatetime
     */
    public function add_days($days)
    {
        
        if ($days < 0) {
            return $this->sub_days($days * -1);
        }
        
        if ($this->flag_jours_ouvres == true) {
            for ($d = 0; $d < $days;) {
                $this->time += self::$TIME2SEC['d'];
                $this->set_timestamp($this->time);
                
                if ($this->est_jours_ouvres()) {
                    $d++;
                }
            }
        } else {
            $this->time += $days * self::$TIME2SEC['d'];
            $this->set_timestamp($this->time);
        }
        
        return $this;
    }
    
    /**
     * Retranche nb jours
     *
     * @param int $days
     *
     * @return awcDatetime
     */
    public function sub_days($days)
    {
        if ($this->flag_jours_ouvres == true) {
            for ($d = $days; $d > 0;) {
                $this->time -= self::$TIME2SEC['d'];
                $this->set_timestamp($this->time);
                
                if ($this->est_jours_ouvres()) {
                    $d--;
                }
            }
        } else {
            $this->time -= $days * self::$TIME2SEC['d'];
            $this->set_timestamp($this->time);
        }
        
        return $this;
    }
    
    /**
     * Ajoute nb heures
     *
     * @param int $hours
     *
     * @return awcDatetime
     */
    public function add_hours($hours)
    {
        $this->time += $hours * self::$TIME2SEC['h'];
        $this->set_timestamp($this->time);
        
        return $this;
    }
    
    /**
     * Ajoute nb minutes
     *
     * @param int $min
     *
     * @return awcDatetime
     */
    public function add_min($min)
    {
        $this->time += $min * self::$TIME2SEC['m'];
        $this->set_timestamp($this->time);
        
        return $this;
    }
    
    /**
     * Ajoute nb mois
     *
     * @param int $months
     *
     * @return awcDatetime
     */
    public function add_month($months)
    {
        $this->time = strtotime("+$months months", $this->time);
        $this->set_timestamp($this->time);
        
        return $this;
    }
    
    /**
     * Retranche nb mois
     *
     * @param int $months
     *
     * @return awcDatetime
     */
    public function sub_month($months)
    {
        $this->time = strtotime("-$months months", $this->time);
        $this->set_timestamp($this->time);
        
        return $this;
    }
    
    /**
     * Teste si une ann�e est bissextile
     * @return boolean
     */
    public function est_bissextile()
    {
        return $this->format('L');
    }
    
    /*
     * BUILDERS
     */
    
    /**
     * Retourne le timestamp de l'objet cDateTime
     * @return int
     */
    public function get_ts()
    {
        return $this->time;
    }
    
    public function get_year()
    {
        if ($this->time == null) {
            return false;
        }
        
        return $this->format('Y');
    }
    
    public function get_month()
    {
        if ($this->time == null) {
            return false;
        }
        
        return $this->format('m');
    }
    
    public function get_day()
    {
        if ($this->time == null) {
            return false;
        }
        
        return $this->format('d');
    }
    
    /**
     * Retourne le num�ro de semaine
     * @return int
     */
    public function get_week()
    {
        if ($this->time == null) {
            return false;
        }
        
        return $this->format('W');
    }
    
    /**
     * Retourne le jour de l'ann�e (0..365)
     * @return int
     */
    public function get_dayofyear()
    {
        if ($this->time == null) {
            return false;
        }
        
        return $this->format('z');
    }
    
    /**
     * Retourne le jour de la semaine (en francais)
     * @return string
     */
    public function get_dayofweek()
    {
        if ($this->time == null) {
            return false;
        }
        
        return self::$longday[$this->adate['weekday']];
    }
    
    public function get_daysInMonth()
    {
        if ($this->time == null) {
            return false;
        }
        
        return $this->adate['daysInMonth'];
    }
    
    public function get_premier_jour_ouvre()
    {
        for ($j = 1; $j <= 3; $j++) {
            $this->set_timestamp(mktime(0, 0, 0, date('m'), $j, date('Y')));
            if ($this->est_jours_ouvres()) {
                break;
            }
        }
        
        return $j;
    }
}
