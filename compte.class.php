<?php

class CT {

// --- Partie privée : les variables

    var $champs, $valeurs, $champsValeurs, $idx, $nomTable;

    var $ct_id, $ct_no, $ct_nom, $ct_genre, $ct_niveau, $ct_rdr;

    var $enregis = array("ct_no", "ct_nom", "ct_genre", "ct_niveau", "ct_rdr");

    /* fonctions accesseurs */

    function set_ct_no ($ct_no) { $this->ct_no = Clean($ct_no); }
    function get_ct_no () { return $this->ct_no; }

    function set_ct_nom ($ct_nom) { $this->ct_nom = Clean($ct_nom); }
    function get_ct_nom () { return $this->ct_nom; }

    function set_ct_genre ($ct_genre) { $this->ct_genre = Clean($ct_genre); }
    function get_ct_genre () { return $this->ct_genre; }

    function set_ct_niveau ($ct_niveau) { $this->ct_niveau = Clean($ct_niveau); }
    function get_ct_niveau () { return $this->ct_niveau; }

    function set_ct_rdr ($ct_rdr) { $this->ct_rdr = Clean($ct_rdr); }
    function get_ct_rdr () { return $this->ct_rdr; }
          
//---- partie publique : les méthodes
          
     Function CT ($table) { // constructeur
          
          /*********************************************************
           liste des champs de l'enregistrement
           le champ « ct_id » n'est pas inclus à cause de la
           fonction MAJ qui ne doit pas comprendre la clé primaire
          ********************************************************/
          
       $this->i = 0;
          
       foreach($this->enregis as $value) {
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
     } //---------------------- constructeur pour CT
          
          
     Function Imprimer_form ($action, $css)
		{
            $listeRdr[1] = "oui";
            $listeRdr[0] = "non";
			print('<table border="0" bgcolor="#EBEBEB" align="center"><tr><td>');
			print(Imprime_titreListe("Gestion d'un compte", "titre"));
			print("<br>");
			$f = new Formulaire ("POST", "comptes.php");
			$f->debutTable(HORIZONTAL);
			$f->champTexte("&nbsp;no du compte", "ct_no", $this->ct_no, 15, 15);
			$f->champTexte("&nbsp;nom du compte", "ct_nom", $this->ct_nom, 50, 50);
			$f->champTexte("&nbsp;0 = d&eacute;penses<br />&nbsp;1 = revenus", "ct_genre", $this->ct_genre, 12, 1);
			$f->champTexte("&nbsp;1 = niveau titre<br />&nbsp;2 = niveau compte", "ct_niveau", $this->ct_niveau, 13, 1);
            $f->champListe("&nbsp;rdr", "ct_rdr", $this->ct_rdr, 2, $listeRdr);
			$f->champCache("ct_id", $this->ct_id);
			$f->finTable();
			$f->debutTable(HORIZONTAL);
			$f->champValider ("sauver", "action");
			$f->finTable();
			$f->fin();
			print('</td></tr></table>');
		} // fin Imprimer_form -----------------------
          
          
     Function Sauver ($bd)
           {
           $requete = "SELECT ct_id FROM $this->nomTable WHERE ct_id = '$this->ct_id' ";
           $res = $bd->execRequete($requete);
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
          
           //----- clé primaire « ct_id » placée au début des champs existants
           //----- « 0 » comme valeur correspondante dans les valeurs
           $this->champs = "ct_id, " . $this->champs;     
           $this->valeurs = "0, " . $this->valeurs;
          
           $requete = "INSERT INTO $this->nomTable (" . $this->champs . ") VALUES (" . $this->valeurs . ")";
           $res = $bd->execRequete($requete);
           $this->ct_id = mysql_insert_id();      //--- pour initialiser « ct_id » dans l'objet après l'insertion dans la BD
           $this->operation = "opération INSÉRER ok __ $this->ct_no - $this->ct_nom __ (enr. $this->ct_id)";
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
           $requete = "UPDATE $this->nomTable SET " . $this->champsValeurs . " WHERE ct_id = '$this->ct_id' " ;
           $resultat = $bd->execRequete($requete);
           $this->operation = "opération MAJ ok __ $this->ct_no - $this->ct_nom __ (enr. $this->ct_id)";
           return $resultat;
           } //---------------------------------------------------------- Maj
          
     Function Detruire ($bd)
           {
           $requete = "DELETE FROM $this->nomTable WHERE ct_id = '$this->ct_id' " ;
           $resultat = $bd->execRequete($requete);
           $this->operation = "opération DÉTRUIRE ok __ $this->ct_no - $this->ct_nom- (enr. $this->ct_id)";
           return $resultat;
           } //---------------------------------------------------------- Detruire
          
     Function Affectation ($ligne, $ct_id)
           {
           foreach($ligne as $index=>$value) {
               if (in_array($index, $this->enregis)) {         // initialiser seulement les éléments
                   $this->{$index} = stripslashes($value); // de $_POST qui font partie de l'enr.
               }
           }
           $this->ct_id = $ct_id; //****** pour initialiser ct_id dans la classe CT
           return;
           } //-------------------------------------------------
          
     Function Get_operation ()
           {
           return $this->operation;
           } //-------------------------------------------
          
     Function Get_ct ($bd, $ct_id)
           {
           $requete = "SELECT * FROM $this->nomTable WHERE ct_id = '$ct_id' ";
           $res = $bd->execRequete($requete);
           if (mysql_num_rows($res) > 0 ) {
               $ligne = $bd->ligneSuivante($res);
               $this->Affectation($ligne, $ct_id);
               $this->operation = "";
           } else {
           $this->ct_id = $ct_id;
           }
           return $res;
           } //-----------------------------
          
           } //---fin de la classe ----------------------------
?> 
