<?php

class PC {

// --- Partie privée : les variables

    var $champs, $valeurs, $champsValeurs, $idx, $nomTable;

    var $pc_id, $ct_id, $pj_id, $pc_prevu, $pc_reel, $pc_engage, $pc_note;

    var $enregis = array("ct_id", "pj_id", "pc_prevu", "pc_reel", "pc_engage", "pc_note");

    /* fonctions accesseurs */

    function set_ct_id ($ct_id) { $this->ct_id = Clean($ct_id); }
    function get_ct_id () { return $this->ct_id; }

    function set_pj_id ($pj_id) { $this->pj_id = Clean($pj_id); }
    function get_pj_id () { return $this->pj_id; }

    function set_pc_prevu ($pc_prevu) { $this->pc_prevu = Clean($pc_prevu); }
    function get_pc_prevu () { return $this->pc_prevu; }

    function set_pc_reel ($pc_reel) { $this->pc_reel = Clean($pc_reel); }
    function get_pc_reel () { return $this->pc_reel; }

    function set_pc_engage ($pc_engage) { $this->pc_engage = Clean($pc_engage); }
    function get_pc_engage () { return $this->pc_engage; }

	function set_pc_note ($pc_note) { $this->pc_note = Clean($pc_note); }
    function get_pc_note () { return $this->pc_note; }

          
//---- partie publique : les méthodes
          
     Function PC ($table) { // constructeur
          
          /*********************************************************
           liste des champs de l'enregistrement
           le champ « pc_id » n'est pas inclus à cause de la
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
           } //---------------------- constructeur pour PC
          
     Function Imprimer_form ($css)
           {
           print('<table border="0" bgcolor="#EBEBEB" align="center"><tr><td>');
           print(Imprime_titreListe("titre de la liste", "titre"));
           print("<br>");
           $f = new Formulaire ("POST", "pcs.php");
           $f->debutTable(VERTICAL);
           $f->champCache("pc_id", $this->pc_id);
           $f->finTable();
           $f->debutTable(HORIZONTAL);
           $f->champValider ("sauver", "action");
           $f->finTable();
           $f->fin();
           print('</td></tr></table>');
           } // fin Imprimer_form -----------------------
          
     Function Sauver ($bd)
           {
           $requete = "SELECT pc_id FROM $this->nomTable WHERE pc_id = '$this->pc_id' ";
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
          
           //----- clé primaire « pc_id » placée au début des champs existants
           //----- « 0 » comme valeur correspondante dans les valeurs
           $this->champs = "pc_id, " . $this->champs;     
           $this->valeurs = "0, " . $this->valeurs;
          
           $requete = "INSERT INTO $this->nomTable (" . $this->champs . ") VALUES (" . $this->valeurs . ")";
           $res = $bd->execRequete($requete);
           $this->pc_id = mysql_insert_id();      //--- pour initialiser « pc_id » dans l'objet après l'insertion dans la BD
           $this->operation = "opération INSÉRER ok __ $this->pj_no __ (enr. $this->pc_id)";
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
           $requete = "UPDATE $this->nomTable SET " . $this->champsValeurs . " WHERE pc_id = '$this->pc_id' " ;
           $resultat = $bd->execRequete($requete);
           $this->operation = "opération MAJ ok __ $this->pj_no __ (enr. $this->pc_id)";
           return $resultat;
           } //---------------------------------------------------------- Maj
          
     Function Detruire ($bd)
           {
           $requete = "DELETE FROM $this->nomTable WHERE pc_id = '$this->pc_id' " ;
           $resultat = $bd->execRequete($requete);
           $this->operation = "opération DÉTRUIRE ok __ $this->pj_no __ (enr. $this->pc_id)";
           return $resultat;
           } //---------------------------------------------------------- Detruire
          
     Function Affectation ($ligne, $pc_id)
           {
           foreach($ligne as $index=>$value) {
               if (in_array($index, $this->enregis)) {         // initialiser seulement les éléments
                   $this->{$index} = stripslashes($value); // de $_POST qui font partie de l'enr.
               }
           }
           $this->pc_id = $pc_id; //****** pour initialiser pc_id dans la classe PC
           return;
           } //-------------------------------------------------
          
     Function Get_operation ()
           {
           return $this->operation;
           } //-------------------------------------------
          
     Function Get_pc ($bd, $pc_id)
           {
           $requete = "SELECT * FROM $this->nomTable WHERE pc_id = '$pc_id' ";
           $res = $bd->execRequete($requete);
           if (mysql_num_rows($res) > 0 ) {
               $ligne = $bd->ligneSuivante($res);
               $this->Affectation($ligne, $pc_id);
               $this->operation = "";
           } else {
           $this->pc_id = $pc_id;
           }
           return $res;
           } //-----------------------------
          
           } //---fin de la classe ----------------------------
?> 