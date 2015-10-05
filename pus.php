<?php
session_start();

/*==========================================================================
« pus.php »
Application : permet la gestion des accès aux projets
Programme fait par Pierre Lavigne     dernière mise à jour : 2014-06-09
Tables utilisées : pusFichiers texte utilisés : aucun
============================================================================
*/

error_reporting(0);

require_once("../utilitaires/defini.php");
require_once("../utilitaires/BD.class.php");
require_once("../utilitaires/Table.php");
require_once("../utilitaires/Formulaire.class.php");
require_once("pvi_fonctions.php");
require_once("pu_class.php");
require_once("budget_fonctions.php");
require_once("../utilitaires/session.php");

$css = "css/formulaire.css";

$bd = NEW BD(USAGER, PASSE, BASE, SERVEUR);

$appli = "gfin";
$session = ControleAcces (SITE . "pus.php", $_REQUEST, session_id(), $bd, $appli, $operation, $css);
if(!$session) exit();

AutosProjets($bd);

//******************** principal ********************************

foreach($_REQUEST as $key=>$value) {
   $$key = stripslashes($value);
}

if (!isset($operation)) $operation = "";

//$listeComptes = ComboComptes($bd);
$listeProjets = ComboProjets($bd);
$listeUsagers = ComboUsagers($bd);

//print_rhtml($listeUsagers);
//exit();

$aujour = date("Y-m-d");
$dateRapport = (trim($_SESSION['dateRapport']) == "") ? $aujour : $_SESSION['dateRapport'];

switch ($action) {

	case "" :
	case "xxx" :  //=============================================================== x x x

		print(Html3("haut", "Budgets", $css));
		
		Menu_budget("cp");

		if(strstr($operation, "!")) print(Imprime_operation3($operation));
		
		print('<table align="center" bgcolor="#EBEBEB"><tr><td>' . "\n");
		print("<br />");
		print(Imprime_titreListe("<b>Gestion des acc&egrave;s </b>", "titre"));
		print("<br />");
		//print('<div align="center"><span class="fsL"><i>Ne pas éditer les <b>PROJETS</b> se terminant par <span class="bcOrange fyN fs">&nbsp;0&nbsp;</span> puisqu\'il s\'agit de titres de <b>domaines</b> ou de <b>sections</b></i></span></div><br />');

		$f = new Formulaire ("post", "pus.php");
		$f->debutTable(HORIZONTAL);
		$f->champListe("&nbsp; chosir un usager de la liste pour rentrer ses projets", usager_id, $usager_id, 1, $listeUsagers);
		$f->champValider ("gestion-acces-pour-cet-usager", "action");
		$f->finTable();
		$f->fin();
		print('</td></tr></table>');

		
		
		print(Html3("bas"));
		break;

	case "gestion-acces-pour-cet-usager" :  //============================================ gestion-acces-

			$usager = ChercheUnUsager($usager_id, $bd);

			print(Html3("haut", "Budgets : ajouter comptes", $css));
		
			Menu_budget("cp");

			print("<br /><table border='0' bgcolor='#EBEBEB' align='center'><tr><td>\n");

			print(Imprime_titreListe("<b>Ac&egrave;s aux projets pour : ". $usager->usager_nom . ", " . $usager->usager_prenom . "</b>", "titre"));

			print('<div align="center"><a href="pus.php?action=xxx" style="text-decoration:underline;color:blue;">choisir un autre usager</a></div><br />');

			print('</td></tr></table>');

			print("<br />");

			print("<br /><table border='0' bgcolor='#EBEBEB' align='center'><tr><td>\n");

			$f = new Formulaire ("post", "pus.php");
			$f->debutTable(HORIZONTAL);
			print(Imprime_titreListe("<b>Ajouter un nouvel acc&egrave;s</b>", "titre"));
			$f->champListe("&nbsp; chosir un projet ", pj_id, $pj_id, 1, $listeProjets);
			$f->champTexte("&nbsp; r&ocirc;le ", role, $role, 30, 30);
			$f->champValider("ajouter-acces-pour-ce-projet", "action");
			$f->champCache("usager_id", $usager_id);
			$f->champCache("aujour", $aujour);
			$f->finTable();
			$f->fin();

			//print('<div align="center"><a href="budgets.php" style="text-decoration+underline; color:blue;">retour &agrave; la page pr&eacute;c&eacute;dente</a></div>');

			print('</td></tr></table>');


			print("<br /><table border='0' bgcolor='#EBEBEB' align='center'><tr><td>\n");

			print(Imprime_titreListe("<b>Acc&egrave;s d&eacute;j&agrave; actifs</b>", "titre"));

			$reqAcces = " SELECT * FROM pus, pjs "
							. " WHERE pus.usager_id = '$usager_id' "
							. "     AND pus.pj_id = pjs.pj_id "
							. " ORDER BY pjs.pj_no ";
			$resAcces = $bd->execRequete($reqAcces);
			$nbAcces = 0;

			while ($unAcces = $bd->objetSuivant($resAcces)) {
				$nbAcces++;
				$entete = ($nbAcces == 1) ? "oui" : "non";
				$f = new Formulaire ("post", "pus.php");
				$f->debutTable(HORIZONTAL);
				
				 // faire une liste d'une seule unité pour éviter que l'utilisateur ne modifie l'unité ******
				//$projet = $unAcces->pj_id;
				//$listeProjet_un = array();
				//$listeProjet_un[$projet] = $listeProjets[$projet];

				if($entete == "oui") {
					$f->champListe("&nbsp; projets <span class=\"bcRouge cBlanc fwB\">&nbsp;ne pas modifier mais plut&ocirc;t cliquer sur ENLEVER&nbsp;</span>", pj_id, $unAcces->pj_id, 1, $listeProjets);
					$f->champTexte("&nbsp; r&ocirc;les", pu_role, $unAcces->pu_role, 30, 30);
				} else {
					$f->champListe("&nbsp;", pj_id, $unAcces->pj_id, 1, $listeProjets);
					$f->champTexte("&nbsp;", pu_role, $unAcces->pu_role, 30, 30);
				}
				$f->champValider ("maj", "action");
				$f->champValider ("enlever", "action");
				$f->champCache("usager_id", $usager_id);
				$f->champCache("pu_id", $unAcces->pu_id);
				$f->finTable();
				$f->fin();
			}

			print('</td></tr></table>');

			print(Html3("bas"));

			break;


	case "ajouter-acces-pour-ce-projet" :  //==========================================  ajouter-ce-compte

		$pu = NEW PU ("pus");
		$pu-> Affectation ($_POST, $pu_id);
		$pu->pu_role = $role;
		$pu->pu_date = $aujour;
		$pu-> Sauver($bd);
		$operation = $pu->Get_operation();
		$url = "location:pus.php?operation=$operation&action=gestion-acces-pour-cet-usager&usager_id=$pu->usager_id";
		header($url);
		exit();
		break;

	case "maj" :  //--------------------------------------------------- m a j

		$pu = NEW PU ("pus");
		$pu-> Affectation ($_POST, $pu_id);
		$pu->pu_date = $aujour;
		$pu-> Sauver($bd);
		$operation = $pu->Get_operation();
		$url = "location:pus.php?operation=$operation&action=gestion-acces-pour-cet-usager&usager_id=$pu->usager_id";
		header($url);
		exit();
		break;

	case "enlever" :  // ======================================  e n l e v e r 
		
		$pu = NEW PU ("pus");
		$pu->Get_pu ($bd, $pu_id);
		$pu->Detruire($bd);
		$operation = "!destruction complétée";
		$url="Location: pus.php?operation=$operation&action=gestion-acces-pour-cet-usager&usager_id=$pu->usager_id";
		header($url);
		exit();
		break;

}

?>