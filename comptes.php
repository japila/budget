<?php
session_start();

/*==========================================================================
« comptes.php »   commentaires:w
Application : permet la gestion des comptes
Programme fait par Pierre Lavigne     dernière mise à jour : 2014-06-09
Tables utilisées : cts
Fichiers texte utilisés : aucun
============================================================================
*/

error_reporting(0);

require_once("../../utilitaires/defini.php");
require_once("../../utilitaires/BD.class.php");
require_once("../../utilitaires/Table.php");
require_once("../../utilitaires/Formulaire.class.php");
require_once("../pvi_fonctions.php");
require_once("compte.class.php");
require_once("budget_fonctions.php");
require_once("../../utilitaires/session.php");
require_once("../pvi_param.php");

$css = "../css/formulaire.css";

$bd = NEW BD(USAGER, PASSE, BASE, SERVEUR);

//print_rhtml($_SESSION);

$appli = "gfin";
$session = ControleAcces (SITE . "budget/comptes.php", $_REQUEST, session_id(), $bd, $appli, $operation, $css);
if(!$session) exit();

AutosProjets($bd);

//******************** principal ********************************

foreach($_REQUEST as $key=>$value) {
   $$key = stripslashes($value);
}

if (!isset($operation)) $operation = "";

$listeComptes = ComboComptes($bd);

$gesAcces = array(1, 33);  // 1=Pierre Lavigne  33= Charles Cormier

if(!in_array($_SESSION['usager_id'], $gesAcces)) {
  $operation = "Vous n'avez pas l'autorisation de modifier les projets et les comptes";
  $url="Location: budgets.php?action=xxx&operation=$operation";
  header($url);
  exit();
}


switch ($action) {
  case "" :
  case "xxx" :
    print(Html3("haut", "Gestion des comptes", $css));
    Menu_budget("cp");
    if(strstr($operation, "!")) print(Imprime_operation3($operation));
    print('<table align="center" bgcolor="#EBEBEB"><tr><td>' . "\n");

    print("<br />");
    print(Imprime_titreListe("<b>Gestion des comptes</b>", "titre"));
    print("<br />");
    $f = new Formulaire ("POST", "comptes.php");
    $f->debutTable(HORIZONTAL);
    $f->champListe("chosir un compte de la liste", ct_id, $ct_id, 1, $listeComptes);
    $f->champValider ("modifier", "action");
    $f->champValider ("detruire", "action");
    $f->champCache("ct_id", $ct_id);
    $f->finTable();
    $f->fin();
    print("<br />");
    print('</td></tr></table>');

    print("<br />");
    print('<table align="center" bgcolor="#EBEBEB"><tr><td>' . "\n");
    $f = new Formulaire ("POST", "comptes.php");
    $f->debutTable(HORIZONTAL);
    $f->champValider ("ajouter-un-nouveau-compte", "action");
    $f->finTable();
    $f->fin();

    print("<br />");
    print('</td></tr></table>');

    print(Html3("bas"));
    break;

  case "ajouter-un-nouveau-compte" :
    $compte = NEW CT("cts");
    print(Html3("haut", "Ajouter compte", $css));
    Menu_budget("cp");
    print("<br />");
    $compte->Imprimer_form($css);
    print(Html3("bas"));
    break;

  case "modifier" :
    $compte = NEW CT("cts");
    print(Html3("haut", "Modif compte", $css));
    print("<br />");
    $compte->Get_ct($bd, $ct_id);
    $compte->Imprimer_form($listeComptes, $css);
    print(Html3("bas"));
    break;

  case "sauver" :
    $compte = NEW CT ("cts");
    $compte->Affectation($_POST, $ct_id);
    $compte->Sauver($bd);
    $operation = $compte->Get_operation();
    $url="Location: comptes.php?action=xxx&operation=$operation";
    unset($compte);
    header($url);
    exit();
    break;

  case "detruire" :
    $compte = NEW CT ("cts");
    $compte->Get_ct ($bd, $ct_id);
    print(Html3("haut", "Destruction compte", $css));
    print('<br /><table align="center" bgcolor="#EBEBEB"><tr><td>' . "\n");
    print("<br />");

    print(Imprime_titreListe("Voulez-vous r&eacute;ellement d&eacute;truire ce compte ?", "ffA fs20 bcRouge cBlanc"));

    $f = new Formulaire ("post", "comptes.php", FALSE, "Form");  
    $f->debutTable(HORIZONTAL);
    $f->champTexte("compte" , "compte_nom" , $compte->ct_nom, 40, 40);
    $f->champCache(ct_id, $ct_id);
    $f->finTable();
    $f->debutTable(HORIZONTAL);
    $f->champValider ("OUI-destruction-compte", "action");
    $f->fin();
    print(Imprime_titreListe("<b>S I N O N</b> &nbsp;=&gt;&nbsp; <a href=\"comptes.php?action=xxx\" style=\"color:blue;\">retour à la fiche du compte</a>", "ffA fs12 fwN"));
    print("</td></tr></table>\n");
    print("<br />");
    print(Html3("bas"));
    break;

  case "OUI-destruction-compte" :
    $compte = NEW CT ("cts");
    $compte->Get_ct ($bd, $ct_id);
    $compte->Detruire($bd);
    $operation = $compte->Get_operation();
    $url="Location: comptes.php?action=xxx&operation=$operation";
    header($url);
    exit();
    break;

}

?>
