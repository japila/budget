<?php
session_start();

/*==========================================================================
« budgets.php »
Application : permet la gestion des budgets
Programme fait par Pierre Lavigne     dernière mise à jour : 2014-06-09
Tables utilisées : pcs, pjs, cts
Fichiers texte utilisés : aucun
============================================================================
*/

//error_reporting(0);

require_once("../../utilitaires/defini.php");
require_once("../../utilitaires/BD.class.php");
require_once("../../utilitaires/Table.php");
require_once("../../utilitaires/Formulaire.class.php");
require_once("../pvi_fonctions.php");
require_once("compte.class.php");
require_once("pc.class.php");
require_once("projet.class.php");
require_once("budget_fonctions.php");
require_once("../../utilitaires/session.php");
require_once("../pvi_param.php");

$css = "../css/formulaire.css:screen;../css/formulaire_print.css:print";

$bd = NEW BD(USAGER, PASSE, BASE, SERVEUR);

$appli = "gfin";
$session = ControleAcces (SITE . "budget/budgets.php", $_REQUEST, session_id(), $bd, $appli, $operation, $css);
if(!$session) exit();

AutosProjets($bd);

foreach($_REQUEST as $key=>$value) {
   $$key = stripslashes($value);
}

if (!isset($operation)) $operation = "";

$listeComptes = ComboComptes($bd);
$listeProjets = ComboProjets($bd);

//print_rhtml($listeProjets);

//exit();

$aujour = date("Y-m-d");

switch ($action) {

	case "" :
	case "xxx" : 

		print(Html3("haut", "Budgets", $css));
		
		Menu_budget("cp", "xx");

		if(strstr($operation, "!")) print(Imprime_operation3($operation));
		
		print('<table align="center" bgcolor="#EBEBEB"><tr><td>' . "\n");
		print("<br />");
		print(Imprime_titreListe("<b>Gestion des budgets </b>", "titre"));
		print("<br />");
		print('<div align="center"><span class="fsL"><i>Ne pas éditer les <b>PROJETS</b> se terminant par <span class="bcOrange fyN fs">&nbsp;0&nbsp;</span> puisqu\'il s\'agit de titres de <b>domaines</b> ou de <b>sections</b></i></span></div><br />');

		$f = new Formulaire ("post", "budgets.php");
		$f->debutTable(HORIZONTAL);
		$f->champListe("&nbsp; chosir un projet de la liste pour rentrer ses comptes", pj_id, $pj_id, 1, $listeProjets);
		$f->champValider ("gestion-du-projet-choisi", "action");
//		$f->champValider ("sommaire", "action");
//		$f->champValider ("detail", "action");
		$f->finTable();
		$f->fin();
		print('</td></tr></table>');

		//if($_SESSION['usager_id'] == 1) Print_rhtml($_SESSION);
				
		print(Html3("bas"));
		break;

	case "changer-date-du-rapport" :

		$_SESSION['dateRapport'] = $dateRapport;
		$url = "Location:budgets.php?action=xxx";
		header($url);
        exit();
		break;

	case "gestion-du-projet-choisi" :  //===================================== gestion-du-projet-choisi

		$reqPj = "SELECT * FROM pjs WHERE pj_id = '$pj_id' ";
 		$resPj = $bd->execRequete($reqPj);
		$unPj = $bd->objetSuivant($resPj);
		unset($resPj);

		print(Html3("haut", "AAQ-edit.proj." .  $unPj->pj_no, $css));
		
		Menu_budget("cp");

		$noProjet = $unPj->pj_no;
		$noProjet = trim($noProjet);
		$nonAjout = FALSE;
		if(substr($noProjet, -1) == "0") {
			$listeComptes = array();
			$listeComptes[] = "pas de comptes pour ce no de projet se terminant par  0";
			$nonAjout = TRUE;
		} 

		print(Imprime_titreListe("<b>Projet $unPj->pj_no - $unPj->pj_nom</b>", "titre"));

		print('<br /><div align="center"><span class="fyI bcJaune cNoire">&nbsp;Lors des <b>pr&eacute;visions budg&eacute;taires</b>, ne rien entrer dans les champs <b>engag&eacute;</b> et <b>r&eacute;el</b></span></div>');
		print("<br /><table border='0' bgcolor='#EBEBEB' align='center'><tr><td>\n");
		$f = new Formulaire ("post", "budgets.php");
		$f->debutTable(HORIZONTAL);
		print(Imprime_titreListe("<b>Ajouter un nouveau compte et ses montants</b>", "titre"));
		$f->champListe("&nbsp; chosir un compte &agrave; ajouter au projet<br /><span class=\"fwB bcRouge cBlanc\">&nbsp;seulement si le compte n'a pas d&eacute;j&agrave; &eacute;t&eacute; s&eacute;lectionn&eacute;&nbsp;</span>", "ct_id", $ct_id, 1, $listeComptes);
		$f->champTexte("&nbsp; pr&eacute;vu", "pc_prevu", $pc_prevu, 9, 9);
		$f->champTexte("&nbsp;<span class='bcJaune cNoire fyI fs11 fwN''>&nbsp;engag&eacute;&nbsp;</span>", "pc_engage", $pc_engage, 9, 9);
		$f->champTexte("&nbsp; <span class='bcJaune cNoire fyI fs11 fwN'>&nbsp;r&eacute;el&nbsp;</span>", "pc_reel", $pc_reel, 9, 9);
		if($nonAjout == FALSE) $f->champValider("ajouter-ce-compte", "action");		
		$f->champCache("pj_id", $pj_id);
		$f->champCache("pc_id", '0');
		$f->finTable();
		$f->debutTable(HORIZONTAL);
		$f->champTexte("&nbsp; ajouter une note pour ce compte <span class='fwN'>(maximum de 200 caract&egrave;res)</span>", "pc_note", $pc_note, 90, 200);
		$f->finTable();
		$f->fin();

		//print('<div align="center"><a href="budgets.php" style="text-decoration+underline; color:blue;">retour &agrave; la page pr&eacute;c&eacute;dente</a></div>');

		print('</td></tr></table>');

		if($nonAjout == FALSE) {
	
			
			//print('<br /><div align="center"><span class="fyI bcOrange cNoire fwN">&nbsp;certains comptes ont été entrés par défaut par le <b>trésorier</b> : enlever ceux qui ne sont pas nécessaires&nbsp;</span></div>');

			if(trim($operation) != "" AND substr($operation, 0, 1) =="!")  print(Imprime_operation3($operation));

			print("<br /><table border='0' bgcolor='#EBEBEB' align='center'><tr><td>\n");
			print(Imprime_titreListe("<b>Comptes et montants d&eacute;j&agrave; entr&eacute;s</b>", "titre"));
			$reqPcs = "SELECT * FROM pcs, pjs, cts "
			              . "WHERE pjs.pj_id = '$pj_id' "
						  . "          AND pjs.pj_id = pcs.pj_id "
						  . "          AND cts.ct_id = pcs.ct_id "
						  ." ORDER BY cts.ct_no" ;
			$resPcs = $bd->execRequete($reqPcs);
			$totreel = 0;
			$totprevu = 0;
			$totengage = 0;
			while ($unPc = $bd->objetSuivant($resPcs)) {
				$f = new Formulaire ("post", "budgets.php");
				$f->debutTable(HORIZONTAL);
				$no_cte = $unPc->ct_id;
				$nom_cte = $listeComptes[$no_cte];
				$no_cte_str = $unPc->ct_no;
				$no_cte_str = (string)$no_cte_str;
				if(substr($no_cte_str, 0, 1) == "5") {
					$nom_cte = str_replace("&nbsp;", "....", $nom_cte);
					$totreel -= $unPc->pc_reel;
					$totprevu -= $unPc->pc_prevu;
					$totengage -= $unPc->pc_engage;
				} else {
					$nom_cte = str_replace("&nbsp;", "", $nom_cte);
					$totreel += $unPc->pc_reel;
					$totprevu += $unPc->pc_prevu;
					$totengage += $unPc->pc_engage;
				}

				$f->champTexte("&nbsp; produits ...... charges", "", $nom_cte, 50, 70);
				$f->champTexte("&nbsp; pr&eacute;vu", "pc_prevu", $unPc->pc_prevu, 9, 9);
				$f->champTexte("&nbsp;<span class='bcJaune cNoire fyI fs11 fwN''>&nbsp;engag&eacute;&nbsp;</span>", "pc_engage", $unPc->pc_engage, 9, 9);
				$f->champTexte("&nbsp;<span class='bcJaune cNoire fyI fs11 fwN''>&nbsp;r&eacute;el&nbsp;</span>", "pc_reel", $unPc->pc_reel, 9, 9);
				$f->champValider ("maj", "action");
				$f->champValider ("enlever", "action");
				$f->champCache("pj_id", $unPc->pj_id);
				$f->champCache("ct_id", $unPc->ct_id);
				$f->champCache("pc_id", $unPc->pc_id);
				$f->finTable();
				$f->debutTable(HORIZONTAL);
				$len_note = strlen($unPc->pc_note);
				$car_ent = ($len_note > 0) ? " caract&egrave;res entrés" : " caract&egrave;re entré"  ;
				$f->champTexte("&nbsp;<span class='fs11'>" . $unPc->ct_no . " &lt;--</span> note pour ce compte  <span class='fwN'>(" . $len_note . $car_ent . " sur un maximum de 200)</span>", "pc_note", $unPc->pc_note, 95, 200);
				print("<br />");
				$f->finTable();
				$f->fin();
			}

			//print('<br />');

			print('<table>');
			print('   <tr>' . "\n");
			print('                <td width="329">&nbsp;</td>' . "\n");
			print('                <td width="92"><div align="left">&nbsp; <span class="fs12 fwN">__________</span></div></td>' . "\n");
			print('                <td width="92"><div align="left">&nbsp; <span class="fs12 fwN">__________</span></div></td>' . "\n");
			print('                <td width="92"><div align="left">&nbsp; <span class="fs12 fwN">__________</span></div></td>' . "\n");
			print('   </tr>');
		
			print('   <tr>' . "\n");
			print('                <td width="329"><div align="right"><span class="fs12 fwB">TOTAUX = </span></div></td>' . "\n");
			print('                <td width="92"><div align="left">&nbsp; &nbsp;<span class="fs12 fwB">' . number_format($totprevu, 2, ',', ' ') . '</span></div></td>' . "\n");
			print('                <td width="92"><div align="left">&nbsp; &nbsp;<span class="fs12 fwB">' . number_format($totengage, 2, ',', ' ') . '</span></div></td>' . "\n");
			print('                <td width="92"><div align="left">&nbsp; &nbsp;<span class="fs12 fwB">' . number_format($totreel, 2, ',', ' ') . '</span></div></td>' . "\n");
			print('    </tr>');
			print('</table>' . "\n");

			print('</td></tr></table>' . "\n");
		}	

		print(Html3("bas"));

		break;

	case "ajouter-ce-compte" :  //==========================================  ajouter-ce-compte

		$reqCte = "SELECT * FROM pcs "
					. "WHERE ct_id = '$ct_id' AND pj_id = '$pj_id' ";
//					. "    AND pcs.pj_no = pjs.pj_no ";
		$resCte = $bd->execRequete($reqCte);
		if(mysql_num_rows($resCte) > 0) {
			$operation = "!<span style=\"font-size:16px;\">vous ne pas entrer ce compte une deuxième fois ici</span>";
			$url = "location:budgets.php?operation=$operation&action=gestion-du-projet-choisi&pj_id=$pj_id";
			header($url);
			exit();
		} else {
			$pc = NEW PC ("pcs");
			$pc-> Affectation ($_POST, $pc_id);
			$pc-> Sauver($bd);
			$operation = $pc->Get_operation();
			$url = "location:budgets.php?operation=$operation&action=gestion-du-projet-choisi&pj_id=$pc->pj_id";
			header($url);
			exit();
		}

		break;

		case "maj" :

		$pc = NEW PC ("pcs");
		$pc-> Affectation ($_POST, $pc_id);
		$pc-> Sauver($bd);
		$operation = $pc->Get_operation();
		$url = "location:budgets.php?operation=$operation&action=gestion-du-projet-choisi&pj_id=$pc->pj_id";
		header($url);
		exit();
		break;

	case "enlever" :  //======================================================  e n l e v e r
        $pc = NEW PC ("pcs");
        $pc->Get_pc ($bd, $pc_id);

		$ct = NEW CT ("cts");
		$ct->Get_ct ($bd, $pc->ct_id);

		$pj = NEW PJ ("pjs");
		$pj->Get_pj ($bd, $pc->pj_id);
		
		print(Html3("haut", "Destruction budget-compte", $css));
	    print('<br /><table align="center" bgcolor="#EBEBEB"><tr><td>' . "\n");
	    print("<br />");

		print(Imprime_titreListe("Projet : $pj->pj_no - $pj->pj_nom", "ffA fs20 bcRouge cBlanc"));

		$f = new Formulaire ("post", "budgets.php", FALSE, "Form");  
	    $f->debutTable(HORIZONTAL);
		$f->champTexte("", "", $ct->ct_no . " - " . $ct->ct_nom, 45, 45);
	    $f->champValider ("OUI-destruction-de-ce-compte", "action");
		$f->champCache("pc_id", $pc_id);
	    $f->fin();
		print("<br /><br />");
	    print(Imprime_titreListe("<b>S I N O N</b> &nbsp;=&gt;&nbsp; <a href=\"budgets.php?action=gestion-du-projet-choisi&pj_id=$pc->pj_id\" style=\"color:blue;\">retour au budget du projet</a>", "ffA fs12 fwN"));
		print("</td></tr></table>\n");
		print("<br />");
		unset($pc);
		unset($ct);
		print(Html3("bas"));
	    break;

	case "OUI-destruction-de-ce-compte" :  // ====================================== d e t ru i r e 
		
		$pc = NEW PC ("pcs");
		$pc->Get_pc ($bd, $pc_id);
		$pc->Detruire($bd);
		$operation = "!destruction complétée";
		$url="Location: budgets.php?action=gestion-du-projet-choisi&operation=$operation&pj_id=$pc->pj_id";
		header($url);
		exit();
		break;


	case "sommaire" :  //====================================================== s o m m a i r e

		$projets = array();
		$dateRapport = (trim($_SESSION['dateRapport']) == "") ? $aujour : $_SESSION['dateRapport'];
	
		print(Html3("haut", "AAQ-Projets-Sommaire", $css));

		print(Menu_budget("cp"));
		
		//print("<br />");

		$wt="100%";

		print('<div id="sommaire">');

		print('<div align="center"><br /><br /><table><tr><td width="' . $wt . '" colspan="4"><div align="center"><span class="ffA fs14 fwB ls1 bcBleu cBlanc">&nbsp;A.A.Q. - Gestion par projets (sommaire) du 2015-05-01 au ' . $dateRapport . '&nbsp; : partie 1&nbsp;</span></div></td></tr></table></div>');

		print("<br />");

		$reqPro = "SELECT * FROM pcs, cts, pjs "
					. "WHERE pcs.ct_id = cts.ct_id "
					. "    AND pcs.pj_id = pjs.pj_id "
					. " ORDER BY pjs.pj_no, cts.ct_no ";
		$resPro = $bd->execRequete($reqPro);

		while($unProjet = $bd->objetSuivant($resPro)) {

			$pj_id = $unProjet->pj_id;
			$pj_no = $unProjet->pj_no;
			$ct_id = $unProjet->ct_id;
			$pc_id = $unProjet->pc_id;

		//	print("pj_id = $pj_id -- pj_no = $pj_no --  ct_id = $ct_id -- pc_id = $pc_id -- ct_no = $unProjet->ct_no -------");
		//	print("p = $unProjet->pc_prevu -- e = $unProjet->pc_engage --  r = $unProjet->pc_reel -----");

			if($unProjet->ct_no > 4999) {
				$prevu = -$unProjet->pc_prevu;
				$reel = -$unProjet->pc_reel;
				$engage = -$unProjet->pc_engage;
			} else {
				$prevu = $unProjet->pc_prevu;
				$reel = $unProjet->pc_reel;
				$engage = $unProjet->pc_engage;
			}
			$note = $unProjet->pc_note;

			$domaine = substr($pj_no, 0, 1); 
			if (!array_key_exists($domaine, $projets)) $projets[$domaine] = $domaine . "00" . ";0;0;0";
			$totaux = explode(";", $projets[$domaine]);
			$totaux[1] += $prevu;
			$totaux[2] += $engage;
			$totaux[3] += $reel;
			$projets[$domaine] = implode(";", $totaux);

			$section = substr($pj_no, 0, 2); 
			if (!array_key_exists($section, $projets)) $projets[$section] = $section . "0" . ";0;0;0";
			$totaux = explode(";", $projets[$section]);
			$totaux[1] += $prevu;
			$totaux[2] += $engage;
			$totaux[3] += $reel;
			$projets[$section] = implode(";", $totaux);

			if(!array_key_exists($pj_no, $projets)) $projets[$pj_no] = $pj_no . ";0;0;0";
			$totaux = explode(";", $projets[$pj_no]);
			$totaux[1] += $prevu;
			$totaux[2] += $engage;
			$totaux[3] += $reel;
			$projets[$pj_no] = implode(";", $totaux);

/*
			if(!array_key_exists('T00', $projets)) $projets['T00'] = 'T00' . ";0;0;0";
			$totaux = explode(";", $projets['T00']);
			$totaux[1] += $prevu;
			$totaux[2] += $engage;
			$totaux[3] += $reel;
			$projets['T00'] = implode(";", $totaux);
*/

		
		}  // while unProjet

		sort($projets);

		$reqSel = "SELECT * FROM pjs WHERE 1 ORDER BY pj_no";
		$resSel = $bd->execRequete($reqSel);

		while ($unProjet = $bd->objetSuivant($resSel)) { 
				$numProjet = $unProjet->pj_no;
				$listes[$numProjet]= $unProjet->pj_no . " - " . $unProjet->pj_nom;
				$pj_id = $unProjet->pj_id;
				$listesPjId[$numProjet] = $pj_id;
		}

		$w1 = "48%";
		$w2 = "13%";
		$w3 = "13%";
		$w4 = "13%";
		$w5 = "13%";

		print('<div align="center"><table width="73%" border="1" cellpadding="5" cellspacing="0">' . "\n\n");
		print('<caption class="projetCaption"><b>Par grands domaines</b></caption>');

		print("   <tr>\n\n");
		print('      <td width="' . $w1 . '" class="enteteProjet"><div align="center"><b>Nom du projet</b>&nbsp; <span class="ffV fs10 tdU fwN"><a href="http://www.archivistes.qc.ca/cora/sg/budget/budgets.php">retour</a></span></div></td>');
		print('      <td width="' . $w2 . '" class="enteteProjet"><div align="center"><b>Pr&eacute;vu</b></div></td>');
		print('      <td width="' . $w3 . '" class="enteteProjet"><div align="center"><b>Engag&eacute;</b></div></td>');
		print('      <td width="' . $w4 . '" class="enteteProjet"><div align="center"><b>R&eacute;el</b></div></td>');
		print('      <td width="' . $w5 . '" class="enteteProjet"><div align="center"><b>&Eacute;cart</b></div></td>');
		print("   </tr>\n\n");

		foreach($projets as $key=>$value) {
		
			list($numPro, $prevu, $engage, $reel) = explode(";", $value);
			$ecart = ($engage + $reel) - $prevu;

			print("   <tr>\n\n");
			
			if(substr($numPro, -2) == "00") {
			
				$total_prevu += $prevu;
				$total_engage += $engage;
				$total_reel += $reel;
				$total_ecart += $ecart;
				$pj_id = $listesPjId[$numPro];

				print('      <td width="' . $w1 . '" class="projetDomaine">' . $listes[$numPro] . '</td>');
				print('      <td  class="projetDomaine" width="' . $w2 . '"><div align="right">' . number_format($prevu, 2, ',', ' ') . '</div></td>');
				print('      <td class="projetDomaine" width="' . $w3 . '"><div align="right">' . number_format($engage, 2, ',', ' ') . '</div></td>');
				print('      <td class="projetDomaine" width="' . $w4 . '"><div align="right">' . number_format($reel, 2, ',', ' ') . '</div></td>');
				if($ecart < 0) {
					print('      <td width="' . $w5 . '" class="projetDomaineEcart"><div align="right">' . number_format($ecart, 2, ',', ' ') . '</div></td>');
				} else {
					print('      <td class="projetDomaine" width="' . $w5 . '"><div align="right"><b>' . number_format($ecart, 2, ',', ' ') .	'</b></div></td>');
				}
			} elseif (substr($numPro, -1) == "0") {
				// rien faire

			} else {
				// rien faire
			
			}

			print("   </tr>\n\n");

		}

		print("   <tr>");

		print('      <td width="' . $w1 . '" class="projetDomaineTotal"><div align="right">totaux de chaque colonne</div></td>');
		print('      <td  class="projetDomaineTotal" width="' . $w2 . '"><div align="right">' . number_format($total_prevu, 2, ',', ' ') . '</div></td>');
		print('      <td class="projetDomaineTotal" width="' . $w3 . '"><div align="right">' . number_format($total_engage, 2, ',', ' ') . '</div></td>');
		print('      <td class="projetDomaineTotal" width="' . $w4 . '"><div align="right">' . number_format($total_reel, 2, ',', ' ') . '</div></td>');
		print('      <td width="' . $w5 . '" class="projetDomaineTotal"><div align="right">' . number_format($total_ecart, 2, ',', ' ') . '</div></td>');

		print("   </tr>\n\n");
		print("</table></div>\n\n");

		print("<h6>&nbsp;</h6>");

		//print("<br /><br />");

		print('<div align="center"><table><tr><td width="' . $wt . '" colspan="4"><div align="center"><span class="ffA fs14 fwB ls1 bcBleu cBlanc">&nbsp;A.A.Q. - Gestion par projets (sommaire) du 2015-05-01 au ' . $dateRapport . '&nbsp; : partie 2&nbsp;</span></div></td></tr></table></div><br />');

		print('<div align="center"><table width="73%" border="1" cellpadding="3" cellspacing="0">' . "\n\n");
		print('<caption class="projetCaption"><b>Par grands domaines et principales fonctions</b></caption>');

		print("   <tr>\n\n");
		print('      <td width="' . $w1 . '" class="enteteProjet"><div align="center"><b>Nom du projet</b>&nbsp; <span class="ffV fs10 tdU fwN"><a href="http://www.archivistes.qc.ca/cora/sg/budget/budgets.php">retour</a></span></div></td>');
		print('      <td width="' . $w2 . '" class="enteteProjet"><div align="center"><b>Pr&eacute;vu</b></div></td>');
		print('      <td width="' . $w3 . '" class="enteteProjet"><div align="center"><b>Engag&eacute;</b></div></td>');
		print('      <td width="' . $w4 . '" class="enteteProjet"><div align="center"><b>R&eacute;el</b></div></td>');
		print('      <td width="' . $w5 . '" class="enteteProjet"><div align="center"><b>&Eacute;cart</b></div></td>');
		print("   </tr>\n\n");

		foreach($projets as $key=>$value) {
		
			list($numPro, $prevu, $engage, $reel) = explode(";", $value);
			$ecart = ($engage + $reel) - $prevu;
			
			print("   <tr>\n\n");
			
			if(substr($numPro, -2) == "00") {
				print('      <td width="' . $w1 . '" class="projetDomaine">' . $listes[$numPro] . '</td>');
				print('      <td  class="projetDomaine" width="' . $w2 . '"><div align="right">' . number_format($prevu, 2, ',', ' ') . '</div></td>');
				print('      <td class="projetDomaine" width="' . $w3 . '"><div align="right">' . number_format($engage, 2, ',', ' ') . '</div></td>');
				print('      <td class="projetDomaine" width="' . $w4 . '"><div align="right">' . number_format($reel, 2, ',', ' ') . '</div></td>');
				if($ecart < 0) {
					print('      <td width="' . $w5 . '" class="projetDomaineEcart"><div align="right">' . number_format($ecart, 2, ',', ' ') . '</div></td>');
				} else {
					print('      <td class="projetDomaine" width="' . $w5 . '"><div align="right"><b>' . number_format($ecart, 2, ',', ' ') .	'</b></div></td>');
				}
			} elseif (substr($numPro, -1) == "0") {

				//$numProjet = substr($numPro, 0, 2) . "0";
				print('      <td width="' . $w1 . '" class="projetSection  projetSectionNom">' . $listes[$numPro] . '</td>');
				print('      <td class="projetSection" width="' . $w2 . '"><div align="right">' . number_format($prevu, 2, ',', ' ') . '</div></td>');
				print('      <td class="projetSection" width="' . $w3 . '"><div align="right">' . number_format($engage, 2, ',', ' ') . '</div></td>');
				print('      <td class="projetSection" width="' . $w4 . '"><div align="right">' . number_format($reel, 2, ',', ' ') . '</div></td>');
				if($ecart < 0) {
					print('      <td width="' . $w5 . '" class="projetSectionEcart"><div align="right"><b>' . number_format($ecart, 2, ',', ' ') . '</b></div></td>');
				} else {
					print('      <td class="projetSection" width="' . $w5 . '"><div align="right"><b>' . number_format($ecart, 2, ',', ' ') . '</b></div></td>');
				}
			} else {
				// rien faire
			
			}

			print("   </tr>\n\n");

		}
		print("</table></div>\n\n");

		print("<h6>&nbsp;</h6>");

		print('<div align="center"><table><tr><td width="' . $wt . '" colspan="4"><div align="center"><span class="ffA fs14 fwB ls1 bcBleu cBlanc">&nbsp;A.A.Q. - Gestion par projets (sommaire) du 2015-05-01 au ' . $dateRapport . '&nbsp; : partie 3&nbsp;</span></div></td></tr></table></div>');

		print("<br />");

		print('<div align="center"><table width="73%" border="1" cellpadding="2" cellspacing="0">' . "\n\n");
		print('<caption class="projetCaption"><b>Par grands domaines, principales fonctions et projets</b></caption>');

		print("   <tr>\n\n");
		print('      <td width="' . $w1 . '" class="enteteProjet"><div align="center"><b>Nom du projet</b>&nbsp; <span class="ffV fs10 tdU fwN"><a href="http://www.archivistes.qc.ca/cora/sg/budget/budgets.php">retour</a></span></div></td>');
		print('      <td width="' . $w2 . '" class="enteteProjet"><div align="center"><b>Pr&eacute;vu</b></div></td>');
		print('      <td width="' . $w3 . '" class="enteteProjet"><div align="center"><b>Engag&eacute;</b></div></td>');
		print('      <td width="' . $w4 . '" class="enteteProjet"><div align="center"><b>R&eacute;el</b></div></td>');
		print('      <td width="' . $w5 . '" class="enteteProjet"><div align="center"><b>&Eacute;cart</b></div></td>');
		print("   </tr>\n\n");

		foreach($projets as $key=>$value) {
		
			list($numPro, $prevu, $engage, $reel) = explode(";", $value);
			$ecart = ($engage + $reel) - $prevu;
			$pj_id = $listesPjId[$numPro];

			print("   <tr>\n\n");
			
			if(substr($numPro, -2) == "00") {
				print('      <td width="' . $w1 . '" class="projetDomaine">' . $listes[$numPro] . '</td>');
				print('      <td  class="projetDomaine" width="' . $w2 . '"><div align="right">' . number_format($prevu, 2, ',', ' ') . '</div></td>');
				print('      <td class="projetDomaine" width="' . $w3 . '"><div align="right">' . number_format($engage, 2, ',', ' ') . '</div></td>');
				print('      <td class="projetDomaine" width="' . $w4 . '"><div align="right">' . number_format($reel, 2, ',', ' ') . '</div></td>');
				if($ecart < 0) {
					print('      <td width="' . $w5 . '" class="projetDomaineEcart"><div align="right">' . number_format($ecart, 2, ',', ' ') . '</div></td>');
				} else {
					print('      <td class="projetDomaine" width="' . $w5 . '"><div align="right"><b>' . number_format($ecart, 2, ',', ' ') .	'</b></div></td>');
				}
			} elseif (substr($numPro, -1) == "0") {

				//$numProjet = substr($numPro, 0, 2) . "0";
				print('      <td width="' . $w1 . '" class="projetSection  projetSectionNom">' . $listes[$numPro] . '</td>');
				print('      <td class="projetSection" width="' . $w2 . '"><div align="right">' . number_format($prevu, 2, ',', ' ') . '</div></td>');
				print('      <td class="projetSection" width="' . $w3 . '"><div align="right">' . number_format($engage, 2, ',', ' ') . '</div></td>');
				print('      <td class="projetSection" width="' . $w4 . '"><div align="right">' . number_format($reel, 2, ',', ' ') . '</div></td>');
				if($ecart < 0) {
					print('      <td width="' . $w5 . '" class="projetSectionEcart"><div align="right"><b>' . number_format($ecart, 2, ',', ' ') . '</b></div></td>');
				} else {
					print('      <td class="projetSection" width="' . $w5 . '"><div align="right"><b>' . number_format($ecart, 2, ',', ' ') . '</b></div></td>');
				}
			} else {                   //#ff7d7d  #ffb366  #b3b3ff  #c1c1ff  #c6c6ff  #d7d7ff  #a8ffa8

				print('      <td width="' . $w1 . '" class="projetNormal projetNormalNom"><a href="budgets.php?action=detail&pj_id=' . $pj_id . '&nbpp=1" target="_blank">' . $listes[$numPro] . '</a></td>');
				//print('      <td width="40%" class="projetNormal projetNormalNom"><a href="budgets.php?action=detail">' . $listes[$numPro] . '</a></td>');
				print('      <td width="' . $w2 . '" class="projetNormal"><div align="right">' . number_format($prevu, 2, ',', ' ') . '</div></td>');
				print('      <td width="' . $w3 . '" class="projetNormal"><div align="right">' . number_format($engage, 2, ',', ' ') . '</div></td>');
				print('      <td width="' . $w4 . '" class="projetNormal"><div align="right">' . number_format($reel, 2, ',', ' ') . '</div></td>');
				if($ecart < 0) {
					print('      <td width="' . $w5 . '" class="projetNormalEcart"><div align="right">' . number_format($ecart, 2, ',', ' ') . '</div></td>');
				} else {
					print('      <td width="' . $w5 . '" class="projetNormal"><div align="right">' . number_format($ecart, 2, ',', ' ') . '</div></td>');
				}
			}
			print("   </tr>\n\n");

		}  // foreach

		print("</table></div>\n\n");

		print("<br /><br />");

		print('</div>');  //  sommaire

		print(Html3("bas"));


		break;  // fin du sommaire

	case "detail" :  //==========================================   d e t a i l

		if($nbpp != 1) {
			print(Html3("haut", "AAQ-Projets-Details", $css));
		} else {
			if($pj_id != 0) {
				$unProjet = $listeProjets[$pj_id];
				$unProjetTrim = $unProjet;
				$unProjetTrim = str_replace("&nbsp;", "", $unProjetTrim);
				$noProjet = substr($unProjetTrim, 0, 3);
				print(Html3("haut", "AAQ-projet $noProjet", $css));
			}
		}

		$dateRapport = (trim($_SESSION['dateRapport']) == "") ? $aujour : $_SESSION['dateRapport'];

		if($pj_id == 0) {
			$reqPro = "SELECT * FROM pcs, cts, pjs "
						. "WHERE pcs.ct_id = cts.ct_id "
						. "    AND pcs.pj_id = pjs.pj_id "
						. " ORDER BY pjs.pj_no, cts.ct_no ";
			$titrePage = "AAQ - Gestion par projets (d&eacute;tail par comptes)";
		} else {
			$reqPro = "SELECT * FROM pcs, cts, pjs "
						. "WHERE pcs.pj_id = '$pj_id' "
						."     AND pcs.ct_id = cts.ct_id "
						. "    AND pcs.pj_id = pjs.pj_id "
						. " ORDER BY pjs.pj_no, cts.ct_no ";
			$titrePage = "AAQ - Gestion d'un projet (d&eacute;tail)";
		}
		$resPro = $bd->execRequete($reqPro);


		$nomProjetTemp = "000";
		$nb = 0;
		$totalProduitsMoinsChargesPrevu = 0;
		$totalProduitsMoinsChargesReel = 0;
		$beneficeProduits = 0;
        $beneficeProduitsPrevu = 0;
		$beneficeProduitsEngReel = 0;
		$beneficeCharges = 0;
        $beneficeChargesPrevu = 0;
		$beneficeChargesEngReel = 0;

		$w1 = "1%";
		$w2 = "25%";
		$w3 = "3%";
		$w4 = "3%";
		$w5 = "3%";
		$w6 = "3%";
		$w7 = "3%";
		$w34 = "6%";
		$w567 = "9%";
		$w67 = "6%";
//		$w34 = "20%";
		$wt = "80%";

		print('<div id="detail">');

		print('<div align="center">');

		print("<br />");

		if($nbpp != 1) {
			print(Menu_budget("cp"));
			print("<br />");
		}

		print('<table border="0" width="90%" bgcolor="#f9f9f9" width="' . $wt . '" cellpadding="1" cellspacing="0">');
		print('<caption class="projetCaption"><b>' . $titrePage . '&nbsp;du 2015-05-01 au ' . $dateRapport . '</b></caption>');

		print('<tr><td width="' . $wt . '" colspan="4">&nbsp;</td></tr>');

		print('<tr>');
		print('   <td width="' . $w1 . '">&nbsp;</td>');
		print('   <td width="' . $w2 . '"&nbsp;</td>');
		print('   <td colspan="2" width="' . $w34 . '"><div align="center" >' . Nbsp(8) .'<span class="fwB fs11 bcBleuPale cNoire ls2">&nbsp;Pr&eacute;vision&nbsp;</span></div></td>');
		print('   <td width="' . $w5 . '">&nbsp;</td>');
		print('   <td colspan="2" width="' . $w67 . '"><div align="center" >' . Nbsp(8) .'<span class="fwB fs11 bcBleuPale cNoire ls2">&nbsp;R&eacute;el&nbsp;</span></div></td>');
		print('</tr>');

		$totalChargesPrevu = 0;
		$totalProduitsPrevu = 0;
		$totalChargesReel = 0;
		$totalProduitsReel = 0;

		while($unProjet = $bd->objetSuivant($resPro)) {

			$nomProjet = $unProjet->pj_nom;

			print('<div id="' . $unProjet->pj_no . '">');

			if($nomProjet != $nomProjetTemp) {

				if($nb > 0) {

					print('<tr>');
					print('   <td width="' . $w1 . '">&nbsp;</td>');
					print('   <td width="' . $w2 . '">&nbsp;</td>');
					print('   <td width="' . $w3 . '"><div align="right"><u>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </u></div></td>');
					print('   <td width="' . $w4 . '"><div align="right"><u>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </u></div></td>');
					print('   <td width="' . $w5 . '">&nbsp;</td>');
					print('   <td width="' . $w6 . '"><div align="right"><u>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </u></div></td>');
					print('   <td width="' . $w7 . '"><div align="right"><u>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </u></div></td>');
					print('</tr>');

					$produitsMoinsChargesPrevu = $totalProduitsPrevu - $totalChargesPrevu;  
					$totalProduitsMoinsChargesPrevu += $produitsMoinsChargesPrevu;
					$produitsMoinsChargesReel = $totalProduitsReel - $totalChargesReel;  
					$totalProduitsMoinsChargesReel += $produitsMoinsChargesReel;

					print('   <tr>');
					print('      <td width="' . $w1 . '">&nbsp;</td>');
					print('      <td width="' . $w2 . '">&nbsp;</td>');
					print('      <td width="' . $w3 . '"><div align="right"><span class="fwB fs11">' . number_format($totalProduitsPrevu, 2, ',', ' ') . '</span>&nbsp;</div></td>');
					print('      <td width="' . $w4 . '"><div align="right"><span class="fwB fs11">' . number_format($totalChargesPrevu, 2, ',', ' ') . '</span>&nbsp;</div></td>');
					print('      <td width="' . $w5 . '">&nbsp;</td>');
					print('      <td width="' . $w6 . '"><div align="right"><span class="fwB fs11">' . number_format($totalProduitsReel, 2, ',', ' ') . '</span>&nbsp;</div></td>');
					print('      <td width="' . $w7 . '"><div align="right"><span class="fwB fs11">' . number_format($totalChargesReel, 2, ',', ' ') . '</span>&nbsp;</div>');
					print('   </tr>');

					print('   <tr>');
					print('      <td width="' . $w1 . '">&nbsp;</td>');
					print('      <td width="' . $w2 . '"><div align="right"><b>PRODUITS moins CHARGES  <span class="fwN">(pr&eacute;vision ... r&eacute;el)</span> :</b></div></td>');
					
					print('      <td colspan="2" width="' . $w34 . '"><div align="center"> &nbsp; &nbsp; &nbsp; &nbsp;');
					$plusMoinsStyle = ($produitsMoinsChargesPrevu < 0) ? "bcOrange cNoire fwB fs11" : "bcVert cBlanc fwB fs11";
					print('        <span class="' . $plusMoinsStyle . '">&nbsp;' . number_format($produitsMoinsChargesPrevu, 2, ',', ' ') . '&nbsp;</span></div></td>');
					print('   <td width="' . $w5 . '"<div align="center">&nbsp; . . .</div></td>');
					print('   <td colspan="2" width="' . $w67 . '"><div align="center"> &nbsp; &nbsp; &nbsp; ');
					$plusMoinsStyle = ($produitsMoinsChargesReel < 0) ? "bcOrange cNoire fwB fs11" : "bcVert cBlanc fwB fs11";
					print('        <span class="' . $plusMoinsStyle . '">&nbsp;' . number_format($produitsMoinsChargesReel, 2, ',', ' ') . '&nbsp;</span></div></td>');
					print('   </tr>');
					print('   <tr><td colspan="7" width="' . $wt . '">&nbsp;</td></tr>');
					
					print("\n" . '   <tr>' . "\n");
					print('      <td width="' . $w1 . '">&nbsp;</td>' . "\n");
					print('      <td width="' . $w2 . '">&nbsp;</td>' . "\n");
					print('      <td colspan="2" width="' . $w34 . '"><div align="right"><b>R&Eacute;EL moins PR&Eacute;VISION :</b></div></td>' . "\n");
					print('      <td colspan="3" width="' . $w567 . '"><div align="left">' . "\n");
					$ecartReelPrevu = $produitsMoinsChargesReel - $produitsMoinsChargesPrevu;
					$plusMoinsStyle = ($ecartReelPrevu < 0) ? "bcOrange cNoire fwB fs11" : "bcVert cBlanc fwB fs11";
					print('           &nbsp; &nbsp;<span class="' . $plusMoinsStyle . '">&nbsp;' . number_format($ecartReelPrevu, 2, ',', ' ') . '&nbsp;</span>' . "\n");
					print('       </div></td>' . "\n");
					//print('      <td width="' . $w6 . '">&nbsp;</td>');
					//print('      <td width="' . $w7 . '">&nbsp;</td>');
					//print('      </td>');
					print('   </tr>' . "\n");
					print('   <tr><td colspan="7" width="' . $wt . '">&nbsp;</td></tr>' . "\n");
					print('   <tr><td colspan="7" width="' . $wt . '">&nbsp;</td></tr>' . "\n");
				}

				$gesAcces = array(1, 33);  // 1=Pierre Lavigne   33=Charles Cormier
				$usager_id = $_SESSION['usager_id'];
				$reqAP = "SELECT * FROM pus, usagers "
									. "WHERE pus.pj_id = '$unProjet->pj_id' "
									. "    AND usagers.usager_id = '$usager_id' "
									. "    AND pus.usager_id = usagers.usager_id ";
				$resAP = $bd->execRequete($reqAP);
				$nbResAP = mysql_num_rows($resAP);

				print('<tr>');
				print('   <td width="' . $w1 . '">&nbsp;</td>');
				if($nbResAP > 0 OR in_array($usager_id, $gesAcces) ) {
					print('   <td width="' . $w2 . '"<span class="fwB fs13 bcBleuPale cNoire">&nbsp;<a href="http://www.archivistes.qc.ca/cora/sg/budget/budgets.php?action=gestion-du-projet-choisi&pj_id=' . $unProjet->pj_id . '" <span style="text-decoration:underline;color:blue;" target="_blank">' . $unProjet->pj_no . ' - ' . $unProjet->pj_nom . '</span></a></span></td>');
				} else {
					print('   <td width="' . $w2 . '"<span class="fwB fs13 bcBleuPale cNoire">&nbsp;' . $unProjet->pj_no . ' - ' . $unProjet->pj_nom . '</span></a></span></td>');
				}
				print('   <td width="' . $w3 . '"><div align="right" ><span class="fwB fs11 bcBleuPale cNoire">&nbsp;Produits&nbsp;</span>&nbsp;</div></td>');
				print('   <td width="' . $w4 . '"><div align="right"><span class="fwB fs11 bcBleuPale cNoire">&nbsp;Charges&nbsp;</span>&nbsp;</div></td>');
				print('   <td width="' . $w5 . '">&nbsp;</td>');
				print('   <td width="' . $w6 . '"><div align="right" ><span class="fwB fs11 bcBleuPale cNoire">&nbsp;Produits&nbsp;</span>&nbsp;</div></td>');
				print('   <td width="' . $w7 . '"><div align="right"><span class="fwB fs11 bcBleuPale cNoire">&nbsp;Charges&nbsp;</span>&nbsp;</div></td>');
				print('</tr>');
				$nomProjetTemp= $unProjet->pj_nom;
				$totalChargesPrevu = 0;
				$totalProduitsPrevu = 0;
				$totalChargesReel = 0;
				$totalProduitsReel = 0;
				$nb++;
			}
	
			if($unProjet->ct_no > 4999) {
				$prevu = $unProjet->pc_prevu;
				$reel = $unProjet->pc_reel + $unProjet->pc_engage;
				$totalChargesPrevu += $prevu;
				$totalChargesReel += $reel;
				$beneficeCharges += $unProjet->pc_reel;
                $beneficeChargesPrevu += $unProjet->pc_prevu;
				$beneficeChargesEngReel += $reel;
			} else {
				$prevu = $unProjet->pc_prevu;
				$reel = $unProjet->pc_reel + $unProjet->pc_engage;
				$totalProduitsPrevu += $prevu;
				$totalProduitsReel += $reel;
				$beneficeProduits += $unProjet->pc_reel;
                $beneficeProduitsPrevu += $unProjet->pc_prevu;
				$beneficeProduitsEngReel += $reel;
			}

			$pc_note = $unProjet->pc_note;
			$lien_note = (trim($pc_note) != "") ? '&nbsp;[<a href="#" style="font-style:italic;font-size:10px;color:blue;" title="' . $pc_note . '">montrer note</a>]'  : "&nbsp;";

			if($unProjet->ct_no > 4999) {
				print('<tr>');
				print('   <td width="' . $w1 . '">&nbsp;</td>');
				print('   <td width="' . $w2 . '">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<span class="pjtable fwN fs11">' . $unProjet->ct_nom . '<span class="fsXS"> (' . $unProjet->ct_no . ')</span></span>' . $lien_note . '</td>');
				print('   <td width="' . $w3 . '">&nbsp;</td>');
				print('   <td width="' . $w4 . '"><div align="right"><span class="fwN fs11">' . number_format($prevu, 2, ',', ' ') . '&nbsp;</span></div></td>');
				print('   <td width="' . $w5 . '">&nbsp;</td>');
				print('   <td width="' . $w6 . '">&nbsp;</td>');
				print('   <td width="' . $w7 . '"><div align="right"><span class="fwN fs11">' . number_format($reel, 2, ',', ' ') . '&nbsp;</span></div></td>');
				print('</tr>');
			} else {
				print('<tr>');
				print('   <td width="' . $w1 . '">&nbsp;</td>');
				print('   <td width="' . $w2 . '">&nbsp; &nbsp; <span class="pjtable fwN fs11">' . $unProjet->ct_nom . '<span class="fsXS"> (' . $unProjet->ct_no . ')</span></span>' . $lien_note . '</td>');
				print('   <td width="' . $w3 . '"><div align="right"><span class="pjtable fwN fs11">' . number_format($prevu, 2, ',', ' ') . '&nbsp;</span></div></td>');
				print('   <td width="' . $w4 . '">&nbsp;</td>');
				print('   <td width="' . $w5 . '">&nbsp;</td>');
				print('   <td width="' . $w6 . '"><div align="right"><span class="pjtable fwN fs11">' . number_format($reel, 2, ',', ' ') . '&nbsp;</span></div></td>');
				print('   <td width="' . $w7 . '">&nbsp;</td>');
				print('</tr>');
			}
			print('</tr>');

			print('</div>');

		}  // fin while unProjet

		print('<tr>');
		print('   <td width="' . $w1 . '">&nbsp;</td>');
		print('   <td width="' . $w2 . '">&nbsp;</td>');
		print('   <td width="' . $w3 . '"><div align="right"><u>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </u></div></td>');
		print('   <td width="' . $w4 . '"><div align="right"><u>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </u></div></td>');
		print('   <td width="' . $w5 . '">&nbsp;</td>');
		print('   <td width="' . $w6 . '"><div align="right"><u>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </u></div></td>');
		print('   <td width="' . $w7 . '"><div align="right"><u>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </u></div></td>');
		print('</tr>');

		$produitsMoinsChargesPrevu = $totalProduitsPrevu - $totalChargesPrevu;
		$totalProduitsMoinsChargesPrevu += $produitsMoinsChargesPrevu;
		$produitsMoinsChargesReel = $totalProduitsReel - $totalChargesReel;
		$totalProduitsMoinsChargesReel += $produitsMoinsChargesReel;

		print('   <tr>');
		print('      <td width="' . $w1 . '">&nbsp;</td>');
		print('      <td width="' . $w2 . '">&nbsp;</td>');
		print('      <td width="' . $w3 . '"><div align="right"><span class="fwB fs11">' . number_format($totalProduitsPrevu, 2, ',', ' ') . '</span>&nbsp;</div></td>');
		print('      <td width="' . $w4 . '"><div align="right"><span class="fwB fs11">' . number_format($totalChargesPrevu, 2, ',', ' ') . '</span>&nbsp;</div></td>');
		print('      <td width="' . $w5 . '">&nbsp;</td>');
		print('      <td width="' . $w6 . '"><div align="right"><span class="fwB fs11">' . number_format($totalProduitsReel, 2, ',', ' ') . '</span>&nbsp;</div></td>');
		print('      <td width="' . $w7 . '"><div align="right"><span class="fwB fs11">' . number_format($totalChargesReel, 2, ',', ' ') . '</span>&nbsp;</div>');
		print('   </tr>');

		print('   <tr>');
		print('      <td width="' . $w1 . '">&nbsp;</td>');
		print('      <td width="' . $w2 . '"><div align="right"><b>PRODUITS moins CHARGES <span class="fwN">(pr&eacute;vision ... r&eacute;el)</span> :</b></div></td>');
		print('      <td colspan="2" width="' . $w34 . '"><div align="center"> &nbsp; &nbsp; &nbsp; ');
		$plusMoinsStyle = ($produitsMoinsChargesPrevu < 0) ? "bcOrange cNoire fwB fs11" : "bcVert cBlanc fwB fs11";
		print('        <span class="' . $plusMoinsStyle . '">&nbsp;' . number_format($produitsMoinsChargesPrevu, 2, ',', ' ') . '&nbsp;</span></div></td>');
		print('   <td width="' . $w5 . '"><div align="center"> &nbsp;  . . .</div></td>');
		print('   <td colspan="2" width="' . $w67 . '"><div align="center"> &nbsp; &nbsp; &nbsp; ');
		$plusMoinsStyle = ($produitsMoinsChargesReel < 0) ? "bcOrange cNoire fwB fs11" : "bcVert cBlanc fwB fs11";
		print('        <span class="' . $plusMoinsStyle . '">&nbsp;' . number_format($produitsMoinsChargesReel, 2, ',', ' ') . '&nbsp;</span></div></td>');
		print('   </tr>');
		print('   <tr><td colspan="7" width="' . $wt . '">&nbsp;</td></tr>');

		print("\n" . '   <tr>' . "\n");
		print('      <td width="' . $w1 . '">&nbsp;</td>' . "\n");
		print('      <td width="' . $w2 . '">&nbsp;</td>' . "\n");
		print('      <td colspan="2" width="' . $w34 . '"><div align="right"><b>R&Eacute;EL moins PR&Eacute;VISION :</b></div></td>' . "\n");
		print('      <td colspan="3" width="' . $w567 . '"><div align="left">' . "\n");
		$ecartReelPrevu = $produitsMoinsChargesReel - $produitsMoinsChargesPrevu;
		$plusMoinsStyle = ($ecartReelPrevu < 0) ? "bcOrange cNoire fwB fs11" : "bcVert cBlanc fwB fs11";
		print('           &nbsp; &nbsp;<span class="' . $plusMoinsStyle . '">&nbsp;' . number_format($ecartReelPrevu, 2, ',', ' ') . '&nbsp;</span>' . "\n");
		print('       </div></td>' . "\n");
					//print('      <td width="' . $w6 . '">&nbsp;</td>');
					//print('      <td width="' . $w7 . '">&nbsp;</td>');
					//print('      </td>');
		print('   </tr>' . "\n");
		print('   <tr><td colspan="7" width="' . $wt . '">&nbsp;</td></tr>' . "\n");
		print('   <tr><td colspan="7" width="' . $wt . '">&nbsp;</td></tr>' . "\n");

		print('</table>');

		print("<br /><br />");

		if($nbpp != 1) {

			$titrePage = "Calcul du b&eacute;n&eacute;fice net";
			print('<table border="0" bgcolor="#f9f9f9" width="60%" cellpadding="1" cellspacing="0">');
			print('<caption class="projetCaption"><b>' . $titrePage . '&nbsp;du 2015-05-01 au ' . $dateRapport . '</b></caption>');
			print('   <tr>');
            print('      <td>&nbsp;</td>');
            print('      <td align="center"><b>Pr&eacute;visions</b></td>');
            print('      <td align="center"><b>R&eacute;el seulement</b></td>');
            print('      <td align="center"><b>R&eacute;el + engagé</b>&nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp;');
            print('      </td>');
            print('</tr>');
			print("   <tr>");
            
			print('      <td align="center">Total des produits</td>');
            print('      <td align="center">' . number_format($beneficeProduitsPrevu, 2, ',', ' ') . '</td>');;
            print('      <td align="center">' . number_format($beneficeProduits, 2, ',', ' ') . '</td>');
            print('      <td align="center">' . number_format($beneficeProduitsEngReel, 2, ',', ' ') . '&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </td>');
			print("   </tr>");
			print("   <tr>");
            
			print('      <td align="center"><u>Total des charges</u></td>');
            print('      <td align="center"><u>' . number_format($beneficeChargesPrevu, 2, ',', ' ') . '</u></td>');
            print('      <td align="center"><u>' . number_format($beneficeCharges, 2, ',', ' ') . '</u></td>');
            print('      <td align="center"><u>' . number_format($beneficeChargesEngReel, 2, ',', ' ') . '</u>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; ');
            print('      </td>');
			print("    </tr>");
			print("   <tr>");

			$beneficeNet = $beneficeProduits - $beneficeCharges;
			$beneficeNetEngReel = $beneficeProduitsEngReel - $beneficeChargesEngReel;
            
			print('      <td align="center"><b>B&eacute;n&eacute;fice net</b></td>');
            print('      <td align="center"><b>' . number_format($totalProduitsMoinsChargesPrevu, 2, ',', ' ') . '</b></td>');
            print('      <td align="center"><b>' . number_format($beneficeNet, 2, ',', ' ') . '</b></td>');
            print('      <td align="center"><b>' . number_format($beneficeNetEngReel, 2, ',', ' ') . '</b>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </td>');
			print("    </tr>");
			print("</table>");
		}

		print("<br /><br />");

		print("</div>");    //  align=center

		print('</div>');  // detail

		print(Html3("bas"));

		break;

	case "deconnexion" :
		
		$usager_id = $_SESSION['usager_id'];
		$requete = " DELETE FROM sessions WHERE usager_id = '$usager_id' ";
		$resDetSession = $bd->execRequete($requete);
		$_SESSION['motDePasse'] = "";
		$_SESSION['autosProjets'] = "";
		$operation = "!vous avez fermé votre session précédente OU elle est expirée";
		$url="Location: budgets.php?action=xxx&operation=$operation";
		header($url);
		exit();
		break;

	case "liste-projets-comptes" :

		$reqAcc = "SELECT * FROM usagers, pjs "
						. "WHERE pjs.usager_id = usagers.usager_id "
//						. "    AND pjs.pj_id = pus.pj_id "
						. "ORDER BY usagers.usager_nom, usagers.usager_prenom ";
		$resAcc = $bd->execRequete($reqAcc);

		$listeAccPro = array();

		while($unAcces = $bd->objetSuivant($resAcc)) {
			$projet_id = $unAcces->pj_id;
			$listeAccPro[$projet_id] .= '<a href="mailto:' . $unAcces->usager_email . '" style="text-decoration:underline;color:blue;">' . $unAcces->usager_nom . ', ' . $unAcces->usager_prenom . '</a>&nbsp;&nbsp;';
		}

		print(Html3("haut", "Projets  et comptes", $css));

		print('<span class="ffV fs13"><b>Listes de projets en date du ' . date("Y-m-d") . '</b> &nbsp; &nbsp; <a href="budgets.php" style="text-decoration:underline;color:blue;">retour à la gestion des budgets</a>');

		foreach($listeProjets as $key=>$value) {
			if(strstr($value, "00") OR strstr(substr($value, 0, 50), "0")) print("<br />");
			print($value);
			$long_valeur = strlen($value);
			if(array_key_exists($key, $listeAccPro)) print (Nbsp(110 - $long_valeur) . $listeAccPro[$key]);
			print("<br />");
		}

		print("<h6>&nbsp;</h6>");

		print("<b>Listes de comptes en date du " . date("Y-m-d") . "</b>");
		foreach($listeComptes as $compte) {
			if (!strstr($compte, "&nbsp;") ) {
				print("<br />$compte<br />");
			} else {
				print($compte . "<br />");
			}
		}

		print("</span>");

		print(Html3("bas"));
       
        break;

    case "cts_analyse" :

        $listeComptes2 = ComboComptes2($bd);
        $totalCte = array();
        $aujour = date("Y-m-d");

		print(Html3("haut", "Analyse par comptes", $css));
		
		Menu_budget("cp", "xx");

        print('<div align="center"><br /><br /><table><tr><td width="80%" colspan="4"><div align="center"><span class="ffA fs14 fwB ls1 bcAqua cNoire">&nbsp;A.A.Q. - Analyse par comptes : donn&eacute;es extraites du <i> Budget de programmes</i> &nbsp;<br />&nbsp; afin de comparer avec ceux de l\'<i>&Eacute;tat des r&eacute;sultats</i> (&nbsp;' . $aujour . '&nbsp;)&nbsp; </span></div></td></tr></table></div>');

		print("<br />");

		$reqPro = "SELECT * FROM pcs, cts, pjs "
					. "WHERE 1 AND pcs.ct_id = cts.ct_id "
					. "    AND pcs.pj_id = pjs.pj_id "
					. " ORDER BY cts.ct_no, pjs.pj_no ";
		$resPro = $bd->execRequete($reqPro);
        //print(mysql_num_rows($resPro));
        
        /*
        for($idx = 4000; $idx < 6000; $idx++) {
            //$totalCte[$idx] = 0;
        }
        */

        print('<div align="center"><table border="1" cellspacing="0", cellpadding=3 width="55%">');
		while($unProjet = $bd->objetSuivant($resPro)) {
            //print($unProjet->ct_no . " - ");
            $ct_no = $unProjet->ct_no;
            $totalCte[$ct_no] +=  $unProjet->pc_reel;
            if($ct_no > 3999 && $ct_no < 5000) $totalProduits += $unProjet->pc_reel;
            if($ct_no > 4999) $totalCharges += $unProjet->pc_reel;
            
        }
        //print_rhtml($totalCte);

        print("   <tr>\n");
        print('      <th>no cte'. "\n");
        print('      <th>intitul&eacute; de compte</th>'. "\n");
        //print('      <th align="right">montant</th>'. "\n");
        print('      <th>produits</th>'. "\n");
        print('      <th>charges</th>'. "\n");
        print('   </tr>');

        foreach($totalCte as $key=>$value) {
           // if($value != 0) {
               print("   <tr>\n");
               print('      <td>' . $key . '</td>'. "\n");
               print('      <td>' . $listeComptes2[$key] . '</td>'. "\n");
               if(substr($key, 0 ,1) == "4") {
                   print('      <td align="right">' . number_format($value, 2, ',', ' ') . '</td>'. "\n");
                   print("      <td>&nbsp;</td>");
               } else {
                   print("      <td>&nbsp;</td>");
                   print('      <td align="right">' . number_format($value, 2, ',', ' ') . '</td>'. "\n");
               }
               print('   </tr>');
               //print($key . "-" . $listeComptes2[$key] . " = " . $value . "<br />");
                //print($key . " = " . $value . "<br />");
            //}
        }
        
       print('<caption> produits = ' . number_format($totalProduits, 2, ',', ' ') .'&nbsp; &nbsp; charges = ' . number_format($totalCharges, 2, ',', ' ') . '</caption>');

       print("</table>\n\n");

        print("<br /><br />");

        print(Html3("bas", $css));
        break;

}

?>