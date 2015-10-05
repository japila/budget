<?php

Function ChercheUnUsager($usager_id, $bd) {

	$reqUsagers = "SELECT * FROM usagers WHERE usager_id ='$usager_id' ";
	$resUsagers = $bd->execRequete($reqUsagers);
	return $bd->objetSuivant($resUsagers);
} // -------------------------

Function ComboUsagers($bd) {

	$reqUsagers = "SELECT * FROM usagers WHERE 1 ORDER BY usager_nom, usager_prenom ";
	$resUsagers = $bd->execRequete($reqUsagers);
	$listeUsagers['0'] = "";
	while ($unUsager = $bd->objetSuivant($resUsagers)) {
		$usager_id = $unUsager->usager_id;
		$listeUsagers[$usager_id] = $unUsager->usager_nom . ", " . $unUsager->usager_prenom;
	}
	return $listeUsagers;
} // --------------------------------------

Function ComboUsagersEmail($bd) {

	$reqUsagers = "SELECT * FROM usagers WHERE 1 ORDER BY usager_nom, usager_prenom ";
	$resUsagers = $bd->execRequete($reqUsagers);
	$listeUsagers['0'] = "";
	while ($unUsager = $bd->objetSuivant($resUsagers)) {
		$usager_id = $unUsager->usager_id;
		$listeUsagersEmail[$usager_id] = $unUsager->usager_email;
	}
	return $listeUsagersEmail;
} // --------------------------------------

Function AutosProjets ($bd) {//***

	//$usager_id = 30;  
	$usager_id = $_SESSION['usager_id'];

	$reqProjet = "SELECT * FROM pus, pjs "
				. "  WHERE pus.usager_id = '$usager_id' "
				. "      AND pus.pj_id = pjs.pj_id ";
	$resProjet = $bd->execRequete($reqProjet);
	$listeAutoProjets = " ";
	while ($unProjet = $bd->objetSuivant($resProjet)) {
       $listeAutoProjets .= $unProjet->pj_no . " ";
    }
	$_SESSION['autosProjets'] = $listeAutoProjets;
} //--------------------------------------------------------

Function Verif_mdp ($choix) {

	print("<br />");
	$affi = $choix;
	$affi = strtoupper($affi);
	$choix = $choix . ".php";
	$f = new Formulaire ("POST", $choix);
	$choix = strtoupper($choix);
	$f->debutTable(HORIZONTAL);
	$f->champMotDePasse('<span class="fs12">entrez le mot de passe pour g&eacute;rer les ' .  $affi . ' ou bien&nbsp;<br />&nbsp;<a href="budgets.php" style="text-decoration:underline; color:blue;">cliquez ici</a> pour continuer si vous n\'en posssédez pas un</span>', mdp, $mdp, 60, 10);
	//$f->champTexte("&nbsp; date des rapports", "dateRapport", $dateRapport, 15, 10);
	$f->champValider ("valider", "action");
	$f->finTable();
	$f->fin();

}  //--------------------------------------------------------------

Function Menu_budget ($choix, $action="") {

	$gesAcces = array(1, 33);  // 1=Pierre Lavigne   33= Charles Cormier

	print("<br />" . "\n");

	print('<div class="taC invisible">' . "\n");
	if(in_array($_SESSION['usager_id'], $gesAcces)) {
		print('&nbsp;<a href="projets.php" class="boutonM">&nbsp;projets&nbsp;</a>&nbsp;' . "\n");
		print('&nbsp;<a href="comptes.php" class="boutonM">&nbsp;comptes&nbsp;</a>&nbsp;' . "\n");
	}
	print('&nbsp;<a href="budgets.php" class="boutonM">&nbsp;<b>B U D G E T S&nbsp;</b></a>&nbsp;' . "\n");
	print('&nbsp;<a href="budgets.php?action=sommaire" class="boutonM">&nbsp;<b>S O M M A I R E</b>&nbsp;</a>&nbsp;' . "\n");
	print('&nbsp;<a href="budgets.php?action=detail"  class="boutonM"><b>&nbsp;D &Eacute; T A I L &nbsp;</b></a>&nbsp;' . "\n");
	print('&nbsp;<a href="budgets.php?action=liste-projets-comptes" class="boutonM">&nbsp;listes p/c&nbsp;</a>&nbsp;' . "\n");
	print('&nbsp;<a href="budgets.php?action=deconnexion" class="boutonM">&nbsp;d&eacute;connexion&nbsp;</a>&nbsp;' . "\n");

	print('&nbsp;<a href="http://www.archivistes.qc.ca/cora/sg/pvi_reunions.php"   class="boutonM">&nbsp;G E S P V I&nbsp;</a>&nbsp;' . "\n");

    if(in_array($_SESSION['usager_id'], $gesAcces)) {
        print('&nbsp;<a href="http://www.archivistes.qc.ca/cora/sg/budget/budgets.php?action=cts_analyse"   class="boutonM">&nbsp;analyse comptes&nbsp;</a>&nbsp;' . "\n");
    }

	if(in_array($_SESSION['usager_id'], $gesAcces)) {
		$f = new Formulaire ("post", "budgets.php");
		$f->debutTable(HORIZONTAL);
        $dateRapport = (trim($_SESSION['dateRapport']) == "") ? date("Y-m-d") :  $_SESSION['dateRapport'];
		$f->champTexte("nouvelle date pour les rapports", "dateRapport", $dateRapport, 25, 15);
		$f->champValider ("changer-date-du-rapport", "action");
		$f->finTable();
		$f->fin();
	}

	print("</div>" . "\n");
	//print("<br />" . "\n");

}  //---------------------------------------

Function ComboComptes ($bd) {//

	$reqSel = "SELECT * FROM cts WHERE 1 ORDER BY ct_no";
	$resSel = $bd->execRequete($reqSel);

    while ($unCompte = $bd->objetSuivant($resSel)) { 
        $ct_id = $unCompte->ct_id;
		$liste['0'] = "";
		if($unCompte->ct_niveau == 3) $indentation = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		if($unCompte->ct_niveau == 2) $indentation = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		if($unCompte->ct_niveau == 1) $indentation = "";

		if($unCompte->ct_niveau == 1) {
			$unCompte->ct_nom= strtoupper($unCompte->ct_nom);
			$unCompte->ct_nom = str_replace("é", "É", $unCompte->ct_nom);
		}
//		if($unCompte->ct_niveau != 1) {
			$liste[$ct_id]= $indentation . $unCompte->ct_no . " - " . $unCompte->ct_nom;
//		} else {
//			$liste[]= $indentation . $unCompte->ct_no . " - " . $unCompte->ct_nom;
//		}
    }
    return $liste;
} //-----------------------------------------------------

Function ComboComptesRdr ($bd) {//

	$reqSel = "SELECT * FROM cts WHERE ct_rdr = '1' ORDER BY ct_no";
	$resSel = $bd->execRequete($reqSel);

    while ($unCompte = $bd->objetSuivant($resSel)) { 
        $ct_id = $unCompte->ct_id;
		$liste['0'] = "";
		if($unCompte->ct_niveau == 3) $indentation = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		if($unCompte->ct_niveau == 2) $indentation = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		if($unCompte->ct_niveau == 1) $indentation = "";

		if($unCompte->ct_niveau == 1) {
			$unCompte->ct_nom= strtoupper($unCompte->ct_nom);
			$unCompte->ct_nom = str_replace("é", "É", $unCompte->ct_nom);
		}
//		if($unCompte->ct_niveau != 1) {
			$liste[$ct_id]= $indentation . $unCompte->ct_no . " - " . $unCompte->ct_nom;
//		} else {
//			$liste[]= $indentation . $unCompte->ct_no . " - " . $unCompte->ct_nom;
//		}
    }
    return $liste;
} //-----------------------------------------------------

Function ComboComptes2 ($bd) {//

	$reqSel = "SELECT * FROM cts WHERE 1 ORDER BY ct_no";
	$resSel = $bd->execRequete($reqSel);

    while ($unCompte = $bd->objetSuivant($resSel)) { 
        $ct_no = $unCompte->ct_no;
		$liste['0'] = "";
        $indentation = "";
		$liste[$ct_no]= $unCompte->ct_nom;
//		} else {
//			$liste[]= $indentation . $unCompte->ct_no . " - " . $unCompte->ct_nom;
//		}
    }
    return $liste;
} //-----------------------------------------------------

Function ComboProjets ($bd) {//-------------------------
	
	$gesAcces = array(1, 21, 33);  // 1=Pierre Lavigne   21=Sylvie Parent  33=Charles Cormier

	$reqSel = "SELECT * FROM pjs WHERE 1 ORDER BY pj_no";
	$resSel = $bd->execRequete($reqSel);
	$liste['0'] = "";
    while ($unProjet = $bd->objetSuivant($resSel)) { 
        $pj_id = $unProjet->pj_id;
		
		$indentation = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		if(substr($unProjet->pj_no, -1) == "0") $indentation = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

		if(substr($unProjet->pj_no, -2) == "00") {
			$indentation = "";
			$unProjet->pj_nom= strtoupper($unProjet->pj_nom);
			$unProjet->pj_nom = str_replace("é", "É", $unProjet->pj_nom);
		}
		if(strstr($_SESSION['autosProjets'], $unProjet->pj_no) OR (in_array($_SESSION['usager_id'], $gesAcces) ) ) {
			$liste[$pj_id]= $indentation . $unProjet->pj_no . " - " . $unProjet->pj_nom;
		}
    }
    return $liste;
} //-----------------------------------------------------


?>