@core @JoursFeries

  Feature: Jours Fériés
    Permet de tester les jours fériés

    Scenario: Nous sommes le 24 décembre
      Given La date est "2018-12-24"
      Then Jour pas ferie

    Scenario: Nous sommes le 25 décembre
      Given La date est "2018-12-25"
      Then Le jour est ferie

    Scenario: Nous sommes le 24 décembre et on ajoute un jour
      Given La date est "2018-12-24"
      And On ajoute un jour
      Then Le jour est ferie

    Scenario: Lundi de paques: Nous sommes le 22 avril 2019
      Given La date est "2019-04-22"
      Then Le jour est ferie

    Scenario: Veille du lundi de paques: Nous sommes le 12 avril 2020
      Given La date est "2020-04-12"
      Then Jour pas ferie

    Scenario: Lundi de paques: Nous sommes le 13 avril 2020
      Given La date est "2020-04-13"
      Then Le jour est ferie

    Scenario: Lundi de paques: Nous sommes le 5 avril 2021
      Given La date est "2021-04-05"
      Then Le jour est ferie
