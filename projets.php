<?php
session_start();

/*==========================================================================
« projets.php »
Application : permet la gestion des projets
Programme fait par Pierre Lavigne     dernière mise à jour : 2014-06-09
Tables utilisées : pjs
Fichiers texte utilisés : aucun
============================================================================
*/

error_reporting(0);

require_once("../../utilitaires/defini.php");
require_once("../../utilitaires/BD.class.php");
require_once("../../utilitaires/Table.php");
require_once("../../utilitaires/Formulaire.class.php");
require_once("../pvi_fonctions.php");
require_once("projet.class.php");
require_once("budget_fonctions.php");
require_once("../../utilitaires/session.php");
require_once("../pvi_param.php");

$css = "../css/formulaire.css";

$bd = NEW BD(USAGER, PASSE, BASE, SERVEUR);

$appli = "gfin";
$session = ControleAcces (SITE . "projets.php", $_REQUEST, session_id(), $bd, $appli, $operation, $css);
if(!$session) exit();

AutosProjets($bd);

//******************** principal ********************************

foreach($_REQUEST as $key=>$value) {
   $$key = stripslashes($value);
}

if (!isset($operation)) $operation = "";

$listeProjets = ComboProjets($bd);
$listeUsagers = ComboUsagers($bd);
$listeUsagersEmail = ComboUsagersEmail($bd);

$gesAcces = array(1, 33);  // 1=Pierre Lavigne  33= Charles Cormier
if(!in_array($_SESSION['usager_id'], $gesAcces)) {
	$operation = "Vous n'avez pas l'autorisation de modifier les projets et les comptes";
	$url="Location: budgets.php?action=xxx&operation=$operation";
	header($url);
    exit();
}

/*
if($_SESSION['motDePasse'] != "bcpmaj24") {
	if(trim($mdp) != "") {
		if($mdp == "bcpmaj24") $_SESSION['motDePasse'] = "bcpmaj24";
		$url = "Location:projets.php";
		header($url);
		exit();
	} else {
		if($_SESSION['motDePasse'] != "bcpmaj24") Verif_mdp("projets");
	}
}
*/
//$_SESSION['dateRapport'] = $dateRapport;

switch ($action) {

/*
	case "maj_passe" :
		if($mdp == "bcpmaj24") {
			$_SESSION['TRUE'];
		}
		$url = "Location:budgets.php";
		header($url);
		exit();
		break;
*/

/*
	case "" :
	case "xxx" :

		print(Html3("haut", "Gestion des projets", $css));
		
		Print(Menu_budget("cp"));

		if(strstr($operation, "!")) print(Imprime_operation3($operation));

		print('<table align="center" bgcolor="#EBEBEB"><tr><td>' . "\n");

		print("<br />");
		print(Imprime_titreListe("<b>Gestion des projets</b>", "titre"));
		print("<br />");
		$f = new Formulaire ("POST", "projets.php");
		$f->debutTable(HORIZONTAL);
		$f->champListe("chosir un projet de la liste", pj_id, $pj_id, 1, $listeProjets);
		$f->champValider ("modifier", "action");
		$f->champValider ("detruire", "action");
		$f->champCache("pj_id", $pj_id);
		$f->finTable();
		$f->fin();
		print('</td></tr></table>');

		print("<br />");
		print('<table align="center" bgcolor="#EBEBEB"><tr><td>' . "\n");
		$f = new Formulaire ("POST", "projets.php");
		$f->debutTable(HORIZONTAL);
		$f->champValider ("ajouter-un-nouveau-projet", "action");
		$f->finTable();
		$f->fin();
		
		print("<br />");

		print('</td></tr></table>');

		//if($_SESSION['usager_id'] == 1) Print_rhtml($_SESSION);

		print(Html3("bas"));
		break;
*/
	case "" :
	case "xxx" :

		$w1 = "7%";
		$w2 = "40%";
		$w3 = "28%";
		$w4 = "10%";
		$w5 = "15%";

		$listeAcces[0] = "non";
		$listeAcces[1] = '<span class="fsXL fwB">oui</span>';

		print(Html3("haut", "Gestion des projets", $css));
		
		Print(Menu_budget("cp"));

		if(strstr($operation, "!")) print(Imprime_operation3($operation));

		print('<div align="center" class="projets">' . "\n");
		print('<table width="800" border="1" cellspacing="0" cellpadding="5">' . "\n");
		print('<caption class="fwB ls2 fs16">Gestion des projets</caption>');
		$requete = "SELECT * FROM pjs"
					. " WHERE 1 "
					. "ORDER BY pj_no ";
		$resultat  = $bd->execRequete($requete);
		$nb = 0;

		print('   <tr bgcolor="#ebebeb">'. "\n");
		print('      <th width="' . $w1 . '">no</th>' . "\n");
		print('      <th width="' . $w2 . '">nom du projet  &nbsp; (<a href="projets.php?action=ajouter-un-nouveau-projet">ajouter-projet</a>)</th>' . "\n");
		print('      <th width="' . $w3 . '">responsable du projet</th>' . "\n");
		print('      <th width="' . $w4 . '">peut<br />&eacute;diter ?</th>' . "\n");
		print('      <th width="' . $w5 . '">action</th>' . "\n");
		print('   </tr>' . "\n");

		while($unProjet = $bd->objetSuivant($resultat)) {
			//$nb++;
			$usager_id = $unProjet->usager_id;
			$acces = $unProjet->pj_acces;
			//$couleur = ($nb % 2 == 0) ? "#ebebeb" : "ffffff";
			$couleur = "";
			$nomProjet = trim($unProjet->pj_nom);
			$noProjet = trim($unProjet->pj_no);
			//print("---$noProjet---<br />");
			if(substr($noProjet, 1, 2) == "00") {
				$nomProjet = $nomProjet;
				$classe = "projetDomaine";
			} else {
				if(substr($noProjet, 2, 1) ==  "0") {
					$nomProjet = '&nbsp;&nbsp;&nbsp;' . $nomProjet; 
					$classe = "projetSection";
				} else {
					$nomProjet = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $nomProjet; 
					$classe = "";
				}
			}
            if(substr($noProjet, 1, 2) == "00") $nomProjet = '<span class="fem11">' . $nomProjet . '</span>';
            if(substr($noProjet, 2, 1) == "0") $nomProjet = '<span class="fem08">' . $nomProjet . '</span>';
			print('   <tr bgcolor="' . $couleur . '">'. "\n");
			print('      <td width="' . $w1 . '" class="' . $classe . " taC" . '">' . $noProjet   . '</td>' . "\n");
			print('      <td width="' . $w2 . '" class="' . $classe . '">' . $nomProjet . '</td>' . "\n");
			print('      <td width="' . $w3 . '" class="' . $classe . '"><a href="mailto:' . $listeUsagersEmail[$unProjet->usager_id] . '" style="color:blue;">' . $listeUsagers[$unProjet->usager_id]. '</a></td>' . "\n");

			if($classe !=  "projetDomaine" AND $classe !=  "projetSection") {
				print('      <td width="' . $w4 . '" class="' . $classe . " taC" . '">' . $listeAcces[$acces] . '</td>' . "\n");
				print('      <td width="' . $w5 . '" class="' . $classe . " taC" . '"><a href="projets.php?action=modifier&pj_id=' . $unProjet->pj_id . '" style="color:blue;">modifier</a>&nbsp; &nbsp;  <a href="projets.php?action=detruire&pj_id=' . $unProjet->pj_id . '" style="color:blue;">&ocirc;ter</a>' . '</td>' . "\n");
			} else {
				print('      <td width="' . $w4 . '" class="' . $classe . '">&nbsp;</td>' . "\n");
				print('      <td width="' . $w5 . '" class="' . $classe . " taC" . '"><span class="fs10 ls0 fwN"><a href="projets.php?action=modifier&pj_id=' . $unProjet->pj_id . '" style="color:blue;">modifier</a>&nbsp; &nbsp;  <a href="projets.php?action=detruire&pj_id=' . $unProjet->pj_id . '" style="color:blue;">&ocirc;ter</a>' . '</span></td>' . "\n");
			}
			print('   </tr>' . "\n");
		}
		print('</table>'. "\n");
		print('</div>' . "\n");
		break;

	case "ajouter-un-nouveau-projet" :
		$projet = NEW PJ("pjs");
		print(Html3("haut", "Ajouter projet", $css));
		print("<br />");
		$projet->Imprimer_form("projets", $css, $listeUsagers);
		print(Html3("bas"));
		break;

     case "modifier" :
        $projet = NEW PJ("pjs");
	    print(Html3("haut", "Modif projet", $css));
		print("<br />");
		$projet->Get_pj($bd, $pj_id);
        $projet->Imprimer_form("projets", $css, $listeUsagers);
        print(Html3("bas"));
	    break;

	case "sauver" :
        $projet = NEW PJ ("pjs");
		$projet->Affectation($_POST, $pj_id);
		$projet->Sauver($bd);
        $operation = $projet->Get_operation();
		$url="Location: projets.php?action=xxx&operation=$operation";
		unset($projet);
		header($url);
        exit();
		break;

	case "detruire" :
        $projet = NEW PJ ("pjs");
        $projet->Get_pj ($bd, $pj_id);
		
		print(Html3("haut", "Destruction projet", $css));
	    print('<br /><table align="center" bgcolor="#EBEBEB"><tr><td>' . "\n");
	    print("<br />");

		print(Imprime_titreListe("Voulez-vous r&eacute;ellement d&eacute;truire ce projet ?", "ffA fs20 bcRouge cBlanc"));

		$f = new Formulaire ("post", "projets.php", FALSE, "Form");  
	    $f->debutTable(HORIZONTAL);
	    $f->champTexte("projet" , "projet_nom" , $projet->pj_nom, 40, 40);
	    $f->champCache(pj_id, $pj_id);
	    $f->finTable();
	    $f->debutTable(HORIZONTAL);
	    $f->champValider ("OUI-destruction-projet", "action");
	    $f->fin();
	    print(Imprime_titreListe("<b>S I N O N</b> &nbsp;=&gt;&nbsp; <a href=\"projets.php?action=xxx\" style=\"color:blue;\">retour à la gestion des projets</a>", "ffA fs12 fwN"));
		print("</td></tr></table>\n");
		print("<br />");
		print(Html3("bas"));
	    break;

	case "OUI-destruction-projet" :
        $projet = NEW PJ ("pjs");
		$projet->Get_pj ($bd, $pj_id);
		$projet->Detruire($bd);
		$operation = $projet->Get_operation();
		$url="Location: projets.php?action=xxx&operation=$operation";
		header($url);
        exit();
		break;

}

?>