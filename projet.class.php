<?php

class PJ {

// --- Partie privée : les variables

    var $champs, $valeurs, $champsValeurs, $idx, $nomTable;

    var $pj_id, $pj_no, $pj_nom, $usager_id, $pj_acces;

    var $enregis = array("pj_no", "pj_nom", "usager_id", "pj_acces");

    /* fonctions accesseurs */

    function set_pj_no ($pj_no) { $this->pj_no = Clean($pj_no); }
    function get_pj_no () { return $this->pj_no; }

    function set_pj_nom ($pj_nom) { $this->pj_nom = Clean($pj_nom); }
    function get_pj_nom () { return $this->pj_nom; }

    function set_pj_usager_id ($pj_usager_id) { $this->pj_usager_id = Clean($pj_usager_id); }
    function get_pj_usager_id () { return $this->pj_usager_id; }

    function set_pj_acces ($pj_acces) { $this->pj_acces = Clean($pj_acces); }
    function get_pj_acces () { return $this->pj_acces; }


//---- partie publique : les méthodes
          
     Function PJ ($table) { // constructeur
          
          /*********************************************************
           liste des champs de l'enregistrement
           le champ « pj_id » n'est pas inclus à cause de la
           fonction MAJ qui ne doit pas comprendre la clé primaire
          ********************************************************/
          
           $this->i = 0;
          
           foreach($this->enregis as $value)
           {
               if ($this->i == 0) {
                   $this->champs .= $value;
               } else {
                   $this->champs .= ", " . $value ;
               }
               $this->{$value} = "";
               $this->i++;
           }
           $this->nomTable = $table;
           return;
           } //---------------------- constructeur pour PJ
          
          
     Function Imprimer_form ($action, $css, $listeUsagers)
		{
			$listeAcces[0] = "non&nbsp; &nbsp; &nbsp; &nbsp; ";
			$listeAcces[1] = "oui";

			print('<table border="0" bgcolor="#EBEBEB" align="center"><tr><td>');
			print(Imprime_titreListe("Gestion d'un projet", "titre"));
			print("<br>");
			$f = new Formulaire ("post", "projets.php");
			$f->debutTable(HORIZONTAL);
			$f->champTexte("no du projet", "pj_no", $this->pj_no, 15, 15);
			$f->champTexte("nom du projet", "pj_nom", $this->pj_nom, 50, 50);
			$f->champListe("nom du responsable", "usager_id", $this->usager_id, 1, $listeUsagers);
			$f->champListe("peut &eacute;diter ?", "pj_acces", $this->pj_acces, 1, $listeAcces);
			$f->champCache("pj_id", $this->pj_id);
			$f->finTable();
			$f->debutTable(HORIZONTAL);
			$f->champValider ("sauver", "action");
			$f->finTable();
			$f->fin();
			print('</td></tr></table>');
		} // fin Imprimer_form -----------------------
          
     Function Sauver ($bd)
           {
           $requete = "SELECT pj_id FROM $this->nomTable WHERE pj_id = '$this->pj_id' ";
           $res = $bd->execRequete($requete);
           if(substr($this->pj_no, 2, 1) == "0") $this->pj_nom = strtoupper($this->pj_nom);
           if (mysql_num_rows($res) > 0 )
               $this->Maj($bd);
           else
               $this->Inserer($bd);
           } //---------------------------------------- sauver
          
     Function Inserer ($bd)
           {
           $this->i = 0;
          
           foreach($this->enregis as $value) {
               if ($this->i == 0) {
                   $this->valeurs = "'" . addslashes($this->{$value}) . "'";
               } else {
                   $this->valeurs .= ", '" . addslashes($this->{$value}) . "'";
               }
               $this->i++;
           }
          
           //----- clé primaire « pj_id » placée au début des champs existants
           //----- « 0 » comme valeur correspondante dans les valeurs
           $this->champs = "pj_id, " . $this->champs;     
           $this->valeurs = "0, " . $this->valeurs;
          
           $requete = "INSERT INTO $this->nomTable (" . $this->champs . ") VALUES (" . $this->valeurs . ")";
           $res = $bd->execRequete($requete);
           $this->pj_id = mysql_insert_id();      //--- pour initialiser « pj_id » dans l'objet après l'insertion dans la BD
           $this->operation = "opération INSÉRER ok __ $this->pj_no __ (enr. $this->pj_id)";
           return;
           } //------------------------------------------------- Inserer
          
     Function Maj ($bd)
           {
           $this->i = 0;
          
           foreach($this->enregis as $value) {
               if ($this->i == 0) {
                   $this->champsValeurs .= $value . " = '" . addslashes($this->{$value}) . "'";
               } else {
                   $this->champsValeurs .= ", " . $value . " = '" . addslashes($this->{$value}) . "'";
               }
               $this->i++;
           }
           $requete = "UPDATE $this->nomTable SET " . $this->champsValeurs . " WHERE pj_id = '$this->pj_id' " ;
           $resultat = $bd->execRequete($requete);
           $this->operation = "opération MAJ ok __ $this->pj_no __ (enr. $this->pj_id)";
           return $resultat;
           } //---------------------------------------------------------- Maj
          
     Function Detruire ($bd)
           {
           $requete = "DELETE FROM $this->nomTable WHERE pj_id = '$this->pj_id' " ;
           $resultat = $bd->execRequete($requete);
           $this->operation = "opération DÉTRUIRE ok __ $this->pj_no __ (enr. $this->pj_id)";
           return $resultat;
           } //---------------------------------------------------------- Detruire
          
     Function Affectation ($ligne, $pj_id)
           {
           foreach($ligne as $index=>$value) {
               if (in_array($index, $this->enregis)) {         // initialiser seulement les éléments
                   $this->{$index} = stripslashes($value); // de $_POST qui font partie de l'enr.
               }
           }
           $this->pj_id = $pj_id; //****** pour initialiser pj_id dans la classe PJ
           return;
           } //-------------------------------------------------
          
     Function Get_operation ()
           {
           return $this->operation;
           } //-------------------------------------------
          
     Function Get_pj ($bd, $pj_id)
           {
           $requete = "SELECT * FROM $this->nomTable WHERE pj_id = '$pj_id' ";
           $res = $bd->execRequete($requete);
           if (mysql_num_rows($res) > 0 ) {
               $ligne = $bd->ligneSuivante($res);
               $this->Affectation($ligne, $pj_id);
               $this->operation = "";
           } else {
           $this->pj_id = $pj_id;
           }
           return $res;
           } //-----------------------------
          
           } //---fin de la classe ----------------------------
?> 