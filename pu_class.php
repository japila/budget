<?php

class PU {

// --- Partie privée : les variables

    var $champs, $valeurs, $champsValeurs, $idx, $nomTable;

    var $pu_id, $usager_id, $pj_id, $pu_date, $pu_role;

    var $enregis = array("usager_id", "pj_id", "pu_date", "pu_role");

    /* fonctions accesseurs */

    function set_usager_id ($usager_id) { $this->usager_id = Clean($usager_id); }
    function get_usager_id () { return $this->usager_id; }

    function set_pj_id ($pj_id) { $this->pj_id = Clean($pj_id); }
    function get_pj_id () { return $this->pj_id; }

    function set_pu_date ($pu_date) { $this->pu_date = Clean($pu_date); }
    function get_pu_date () { return $this->pu_date; }

    function set_pu_role ($pu_role) { $this->pu_role = Clean($pu_role); }
    function get_pu_role () { return $this->pu_role; }

          
//---- partie publique : les méthodes
          
     Function PU ($table) { // constructeur
          
          /*********************************************************
           liste des champs de l'enregistrement
           le champ « pu_id » n'est pas inclus à cause de la
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
           } //---------------------- constructeur pour PU
          
          
     Function Imprimer_form ($parametres, $action)
       {
           print('<table border="0" bgcolor="#EBEBEB" align="center"><tr><td>');
           print(Imprime_titreListe("Acc&egrave;s aux projets", "titre"));
           print("<br>");
           $f = new Formulaire ("POST", "pus.php");
           $f->debutTable(VERTICAL);
           //$f->champTexte("$this->{$value}", "$this->{$value}", $this->{$value}, 3, 58);
           //$f->champListe("chosir un élément de la liste", $this->{variable}, $this->{variable}, 1, $uneListe);
           //$f->champFenetre("fenêtre", $this->{$value}, $this->{$value}, 1, 79);
           $f->champCache("pu_id", $this->pu_id);
           $f->finTable();
           $f->debutTable(HORIZONTAL);
           $f->champValider ("sauver", "action");
           $f->finTable();
           $f->fin();
           print('</td></tr></table>');
       } // fin Imprimer_form -----------------------
          
     Function Sauver ($bd)
           {
           $requete = "SELECT pu_id FROM $this->nomTable WHERE pu_id = '$this->pu_id' ";
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
          
           //----- clé primaire « pu_id » placée au début des champs existants
           //----- « 0 » comme valeur correspondante dans les valeurs
           $this->champs = "pu_id, " . $this->champs;     
           $this->valeurs = "0, " . $this->valeurs;
          
           $requete = "INSERT INTO $this->nomTable (" . $this->champs . ") VALUES (" . $this->valeurs . ")";
           $res = $bd->execRequete($requete);
           $this->pu_id = mysql_insert_id();      //--- pour initialiser « pu_id » dans l'objet après l'insertion dans la BD
           $this->operation = "opération INSÉRER ok -- $this->pu_id -- (enr. $this->pu_id)";
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
           $requete = "UPDATE $this->nomTable SET " . $this->champsValeurs . " WHERE pu_id = '$this->pu_id' " ;
           $resultat = $bd->execRequete($requete);
           $this->operation = "opération MAJ ok -- $this->pu_id -- (enr. $this->pu_id)";
           return $resultat;
           } //---------------------------------------------------------- Maj
          
     Function Detruire ($bd)
           {
           $requete = "DELETE FROM $this->nomTable WHERE pu_id = '$this->pu_id' " ;
           $resultat = $bd->execRequete($requete);
           $this->operation = "opération DÉTRUIRE ok -- $this->pu_id -- (enr. $this->pu_id)";
           return $resultat;
           } //---------------------------------------------------------- Detruire
          
     Function Affectation ($ligne, $pu_id)
           {
           foreach($ligne as $index=>$value) {
               if (in_array($index, $this->enregis)) {         // initialiser seulement les éléments
                   $this->{$index} = stripslashes($value); // de $_POST qui font partie de l'enr.
               }
           }
           $this->pu_id = $pu_id; //****** pour initialiser pu_id dans la classe PU
           return;
           } //-------------------------------------------------
          
     Function Get_operation ()
           {
           return $this->operation;
           } //-------------------------------------------
          
     Function Get_pu ($bd, $pu_id)
           {
           $requete = "SELECT * FROM $this->nomTable WHERE pu_id = '$pu_id' ";
           $res = $bd->execRequete($requete);
           if (mysql_num_rows($res) > 0 ) {
               $ligne = $bd->ligneSuivante($res);
               $this->Affectation($ligne, $pu_id);
               $this->operation = "";
           } else {
           $this->pu_id = $pu_id;
           }
           return $res;
           } //-----------------------------
          
           } //---fin de la classe ----------------------------
?> 