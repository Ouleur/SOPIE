<form class="contact10-form validate-form10" id="formulaire10" method="POST">
    <div class="wrap-input10 validate-input">
        <input id="json" class="input10" type="text" name="json" placeholder="votre fichier json avec l'extention json">
        <span class="focus-input10"></span>
        <label class="label-input10" for="json">
            
        </label>
    </div>


    <div class="wrap-input10 validate-input">
        <input id="phone" class="input10" type="text" name="fichier_crud" placeholder="le fichier CRUD  ">
        <span class="focus-input10"></span>
        <label class="label-input10" for="fichier CRUD">
            
        </label>
    </div>

    <div class="container-contact10-form-btn">
        <input type="submit" id="generer_db_crud" name="generer" value="generer" class="contact10-form-btn">
    </div>
</form>
<?php
//+++++++++++++++++++++++++++++++++ENTETE DE LA BASE DE DONNEES++++++++++++++++++++++++++++++++++++
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
if (isset($_POST["generer"])) 
{
    # code...

	$fichier=$_POST["json"];
	$json = file_get_contents($fichier);
	$json = json_decode($json, true);
	$servername = $json["database"]["ip"];
	
    $username = $json["database"]["user"];
    
    $password = $json["database"]["pass"];
    
    $ddbase=$json["database"]["name"];
    $test=0;
    $creer=0;
    $creer_asso=0;
    $concart_clees_primaire="";
    $reference_clee="";
    $reference_clee="";
//+++++++++++++++++++++++++++++++++++FIN DE ENTETE+++++++++++++++++++++++++++++++++++++++++++++++++++
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

 //++++++++++++++++++++++++++++++CREATION DE LA BASE DE DONNEES++++++++++++++++++++++++++++++++++++++
 //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    try {

            /************************créer la base de donnée****************/

            $conn = new PDO("mysql:host=$servername", $username, $password); //connection au server 
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = "CREATE DATABASE $ddbase";
            // use exec() because no results are returned
            $conn->exec($sql);
            echo "base de donnée céer avec succes<br>";

            /**************************************************************/

        }
        catch(PDOException $e)
        {
	        echo "<tr><td>". $sql . "<td/><td>" . $e->getMessage()."<td/><tr/>";
	        $creer=1; //EN CAS D'ECHEC
        }

    $conn = null;
        /*********************script pour creer les tables*********************/
//*****************************ceer les differentes tables*****************************************
    foreach ($json["tables"] as $tables) 
    {
        if ($test==1) //creation de chaque table
        {
            if (isset($cle_primaire)) //definition de la clee primaire
            {

                $sql_table =$sql_table."PRIMARY KEY (".$cle_primaire."), ";
            }
            if (isset($unique))//ajout des contrainte unique 
            {
                foreach ($unique as $un) 
                {
                    $sql_table =$sql_table."UNIQUE (".$un."), ";
                }
            }
            $sql_table=substr($sql_table,0,-2);
            $sql_table=$sql_table.")ENGINE=InnoDB;";
            try 
            {

                /**********************créer les differentes tables***************/
                $conn_ddbase= new PDO("mysql:host=$servername;dbname=$ddbase", $username, $password); //connection a la base de donnée $ddbase
                $conn_ddbase->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $conn_ddbase->exec($sql_table);



            }
            catch(PDOException $e)
            {
                echo "<tr><td>". $sql_table . "<td/><td>" . $e->getMessage()."<td/>";
                $creer=1;
            }

            $conn = null;
            unset($unique);
            unset($cle_primaire);
            $test=0;
        }
        if(isset($tables["table"])) //recuperer le nom des tables
        {
            $table=$tables["table"];
            $sql_table = "CREATE TABLE ".$table."(";   
        }
        if(isset($tables["colonne"]))//recuperer les attributs de chaque tables
        {
            foreach ($tables["colonne"] as $attribut) {//recuperer les definitions de chaque attributs
                $sql_table=$sql_table.$attribut["name"]." ".$attribut["type"];
                if ($attribut["taile"]!="") {
                    $sql_table=$sql_table."(".$attribut["taile"].") ";
                }
                if ($attribut["auto_increment"]=="true") {
                    if ($attribut["type"]=="INT") {
                        $sql_table=$sql_table." Auto_increment ";
                    } 
                }
                if ($attribut["cle_primaire"]=="true") {
                    if (!isset($cle_primaire)) {
                        $sql_table=$sql_table." NOT NULL ";
                        $cle_primaire=$attribut["name"];
                    }  
                }else{
                    if ($attribut["unique"]=="true") {
                        $sql_table=$sql_table." NOT NULL ";
                        $unique[]=$attribut["name"];
                    }else{
                        if ($attribut["null"]=="false") {
                            $sql_table=$sql_table." NOT NULL ";
                        }

                    }

                }
                $sql_table=$sql_table.", ";      
                $test=1;
            } 
        }
    }
    if ($test==1) //creation de chaque table
    {
        if (isset($cle_primaire)) //definition de la clee primaire
        {
            $sql_table =$sql_table."PRIMARY KEY (".$cle_primaire."), ";
        }
        if (isset($unique)) //ajout des contrainte unique
        {
            foreach ($unique as $un) 
            {
                $sql_table =$sql_table."UNIQUE (".$un."), ";
            }
        }
        $sql_table=substr($sql_table,0,-2);
        $sql_table=$sql_table.")ENGINE=InnoDB;";
        try 
        {

            /**********************créer les differentes tables***************/
            $conn_ddbase= new PDO("mysql:host=$servername;dbname=$ddbase", $username, $password); //connection a la base de donnée $ddbase
            $conn_ddbase->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn_ddbase->exec($sql_table);
        }
        catch(PDOException $e)
        {
            echo"<tr><td>". $sql_table . "<td/><td>" . $e->getMessage()."<td><tr>";
            $creer=1;
        }
        $conn = null;
        unset($unique);
        unset($cle_primaire);
        $test=0;
    }
//****************************fin de creation des tables*********************

//****************************script pour les clee etrangere***********************
    foreach ($json["tables"] as $tables) 
    {
        if (isset($tables["clee_etrangere"])) 
        {
            foreach ($tables["clee_etrangere"] as $propriete) 
            {
            	//definition de l'attribut comme clee etrangère
                $ajout_clee_etrangere="ALTER TABLE ".$propriete["table_fils"]." ADD FOREIGN KEY (".$propriete["clee"].") REFERENCES ".$propriete["table_pere"]."(".$propriete["clee"].");";
                //definition de l'attribut comme un champ de la table fils
                $ajout_champ="ALTER TABLE ".$propriete["table_fils"]." ADD ".$propriete["clee"]." ".$propriete["type"]." NOT NULL;";
                try 
                {

	                /**********************créer les differentes clee etrangere***************/
	                $conn_ddbase= new PDO("mysql:host=$servername;dbname=$ddbase", $username, $password); //connection a la base de donnée $ddbase
	                $conn_ddbase->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	                $conn_ddbase->exec($ajout_champ);
                }
                catch(PDOException $e)
                {
                    echo "<tr><td>".$ajout_champ. "<td/><td>" . $e->getMessage()."<tr/><td/>";
                    $creer=1;
                }
                $conn = null;
                try {

	                /**********************créer les differentes clee etrangere***************/
	                $conn_ddbase= new PDO("mysql:host=$servername;dbname=$ddbase", $username, $password); //connection a la base de donnée $ddbase
	                $conn_ddbase->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	                $conn_ddbase->exec($ajout_clee_etrangere);
                }
                catch(PDOException $e)
                {
                    echo "<tr><td>".$ajout_clee_etrangere . "<td/><td>" . $e->getMessage()."<tr/><td/>";
                    $creer=1;
                }
                $conn = null;
            }
        }
    }

//***************************fincreation de clee etrangere*********************


    /***************fin clees etrangère***************/

//****************************creation des associations************************
if (isset($json["associations"])) 
{

foreach ($json["associations"] as $associations) 
    {
        if ($test==1) //creation de chaque association
        {
            $concart_clees_primaire=substr($concart_clees_primaire,0,-2);
            $concart_clees_primaire=$deb_concart.$concart_clees_primaire."),";
            $reference_clee=substr($reference_clee,0,-2);
            $sql_association=$sql_association.$concart_clees_primaire.$reference_clee.")ENGINE=InnoDB;";
            try 
            {

                /**********************créer les differentes associations***************/
                $conn_ddbase= new PDO("mysql:host=$servername;dbname=$ddbase", $username, $password); //connection a la base de donnée $ddbase
                $conn_ddbase->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $conn_ddbase->exec($sql_association);



            }
            catch(PDOException $e)
            {
                echo "<tr><td>". $sql_association . "<td/><td>" . $e->getMessage()."<td/>";
                $creer=1;
            }

            $conn = null;
            $test=0;
        }
        if(isset($associations["association"])) //recuperer le nom des associations
        {
            $association=$associations["association"];
            $sql_association = "CREATE TABLE ".$association."(";
            $deb_concart="CONSTRAINT ".$association."_PK PRIMARY KEY (";   
        }
        /********* créer les clees etrangère des associations*********/

        if(isset($associations["clee_etrangere"]))//recuperer les attributs de chaque associations
        {
            foreach ($associations["clee_etrangere"] as $attribut) {//recuperer les definitions de chaque attributs
            	$concart_clees_primaire=$concart_clees_primaire.$attribut["clee"].", ";
            	$reference_clee=$reference_clee."CONSTRAINT ".$association."_".$attribut['table']."_FK FOREIGN KEY (".$attribut['clee'].") REFERENCES ".$attribut["table"]."(".$attribut["clee"]."), ";
                $sql_association=$sql_association.$attribut["clee"]." ".$attribut["type"];
                if ($attribut["type"]=="INT" ||$attribut["type"]=="date" || $attribut["type"]=="time") 
                {
                    $sql_association=$sql_association." NOT NULL";
                }else
                {
                	if ($attribut["taile"]!="") 
                	{
                		$sql_association=$sql_association."(".$attribut["taile"].") NOT NULL ";
                	}else
                	{
                		$erreurs[]="les types differents de time et date doivent avoir une taille";
                	}
                }
                $sql_association=$sql_association.", ";      
            } 
            $test=1;
        }

        /***************fin clees etrangère***************/
        if(isset($associations["colonne"]))//recuperer les attributs de chaque associations
        {
            foreach ($associations["colonne"] as $attribut) {//recuperer les definitions de chaque attributs
                $sql_association=$sql_association.$attribut["name"]." ".$attribut["type"];
                if ($attribut["type"]=="date" || $attribut["type"]=="time") 
                {
                    $sql_association=$sql_association."() ";
                }else
                {
                	if ($attribut["taile"]!="") 
                	{
                		$sql_association=$sql_association."(".$attribut["taile"].") ";
                	}else
                	{
                		$erreurs[]="les types differents de time et date doivent avoir une taille";
                	}
                }
                $sql_association=$sql_association.", ";      
            } 
        }
    }
    if ($test==1) //creation de chaque table
    {
        $concart_clees_primaire=substr($concart_clees_primaire,0,-2);
        $concart_clees_primaire=$deb_concart.$concart_clees_primaire."),";
        $reference_clee=substr($reference_clee,0,-2);
        $sql_association=$sql_association.$concart_clees_primaire.$reference_clee.")ENGINE=InnoDB;";
        try 
        {

            /**********************créer les differentes associations***************/
            $conn_ddbase= new PDO("mysql:host=$servername;dbname=$ddbase", $username, $password); //connection a la base de donnée $ddbase
            $conn_ddbase->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn_ddbase->exec($sql_association);
        }
        catch(PDOException $e)
        {
            echo"<tr><td>". $sql_association . "<td/><td>" . $e->getMessage()."<td><tr>";
            $creer=1;
        }
        $conn = null;
        $test=0;
    }
}
//****************************fin creation des associations********************

//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 //++++++++++++++++++++++++++++++FIN CREATION DE LA BASE DE DONNEES++++++++++++++++++++++++++++++++++


//+++++++++++++++++++++++++++++++CREATION DE L'API CRUD DE LA BASE DE DONNEES++++++++++++++++++++++++
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

	if ($creer!=1) 
	{
		$creer=0;
        if (isset($_POST["fichier_crud"])) {
            $fichier_crud=$_POST["fichier_crud"];
            $myfile = fopen($fichier_crud, "w") or die("Unable to open file!");
        }else{
            $myfile = fopen("api_crud.php", "w") or die("Unable to open file!");
        }
        $classe="<?php\n";
        $avoir_table="Tables_in_".$ddbase;
        fwrite($myfile, $classe);
		
		try {

            $conn_ddbase= new PDO("mysql:host=$servername;dbname=$ddbase", $username, $password); //connection a la base de donnée $ddbase
            $conn_ddbase->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db_tables = $conn_ddbase->prepare("show tables");
            $db_tables->execute();
            while($tables = $db_tables->fetch(PDO::FETCH_ASSOC))
            {
                
                $table=$tables[$avoir_table];
                
                $classe="class"." ".$table."\n{\n";
                fwrite($myfile, $classe);
                
                $query = $conn_ddbase->prepare('SHOW COLUMNS from '.$table);
                $query->execute();
                $classe="public function creer(";
                fwrite($myfile, $classe);
                $classe="";
                while($rows = $query->fetch(PDO::FETCH_ASSOC))
                {
                    
                    $nom=$rows["Field"];
                    if ($rows["Key"]=='PRI' ) 
                    {
                        $val[]=$nom;
                        $cle_primaire=$nom;
                    }else 
                    {
                        $classe=$classe."\$".$nom.", ";
                        $val[]=$nom;
                    }                   
                }
                $classe=substr($classe, 0, -2);
                fwrite($myfile, $classe);
                $classe=")\n{
                try
                    {
                    /* On se connecte à MySQL*/
                    \$bdd = new PDO('mysql:host=".$servername.";dbname=".$ddbase.";charset=utf8', '".$username."', '',array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
                    }catch(Exception \$e)
                    {
                    // En cas d'erreur, on affiche un message et on arrête tout
                    die('Erreur : '.\$e->getMessage());
                    } 
                    \$req = \$bdd->prepare('INSERT INTO ";
                fwrite($myfile, $classe);
                $classe=$table."(";
                fwrite($myfile, $classe);
                $valeur="";
                $classe="";
                foreach ($val as $value) 
                {
                    if ($value==$cle_primaire) 
                    {
                        
                    }else{
                        $classe=$classe.$value.", ";
                        $valeur=$valeur.":".$value.", ";
                    }
                    
                }
                $classe=substr($classe, 0, -2);
                $valeur=substr($valeur, 0, -2);
                fwrite($myfile, $classe);
                $classe=") VALUES(".$valeur.")');\n\$creation=\$req->execute(array(";
                fwrite($myfile, $classe);
                $classe="";
                foreach ($val as $value) 
                {
                    if ($value==$cle_primaire) 
                    {
                        
                    }else
                    {
                        $classe=$classe."\n'".$value."' => \$".$value.", ";
                    }
                }
                $classe=substr($classe, 0, -2);
                fwrite($myfile, $classe);
                $classe="));\n";
                fwrite($myfile, $classe);
                $classe="return \$creation;";
                fwrite($myfile, $classe);
                $classe="\n}\n";
                fwrite($myfile, $classe);
                $classe="public function lire()\n{\n";
                fwrite($myfile, $classe);
                $classe="
                try
                    {
                    /* On se connecte à MySQL*/
                    \$bdd = new PDO('mysql:host=".$servername.";dbname=".$ddbase.";charset=utf8', '".$username."', '',array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
                    }catch(Exception \$e)
                    {
                    // En cas d'erreur, on affiche un message et on arrête tout
                    die('Erreur : '.\$e->getMessage());
                    }\n";
                fwrite($myfile, $classe);
                $classe="\$selection = \$bdd->query('SELECT * FROM ".$table."')or die(print_r(\$bdd->errorInfo()));\n";
                fwrite($myfile, $classe);
                $classe="return \$selection;\n";
                fwrite($myfile, $classe);

                $classe="}\n";
                fwrite($myfile, $classe);
                $classe="public function mettre_a_jour(";
                fwrite($myfile, $classe);
                $classe="";
                foreach ($val as $value) 
                    {
                        if ($value==$cle_primaire) 
                        {
                            $classe=$classe."\$".$value.", ";
                        }else
                        {
                            $classe=$classe."\$".$value.", ";
                            $valeur=$valeur.":".$value.", ";
                        }
                    }
                $classe=substr($classe, 0, -2);
                fwrite($myfile, $classe);
                $classe=")\n{\n";
                fwrite($myfile, $classe);
                $classe="
                try
                    {
                    /* On se connecte à MySQL*/
                    \$bdd = new PDO('mysql:host=".$servername.";dbname=".$ddbase.";charset=utf8', '".$username."', '',array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
                    }catch(Exception \$e)
                    {
                    // En cas d'erreur, on affiche un message et on arrête tout
                    die('Erreur : '.\$e->getMessage());
                    } ";
                fwrite($myfile, $classe);
                $classe="\n\$update = \$bdd->query(\"UPDATE ";
                fwrite($myfile, $classe);
                $classe=$table." set ";
                foreach ($val as $value) 
                {
                    if ($value==$cle_primaire) 
                    {
                        
                    }else
                    {
                        $classe=$classe.$value."='\$".$value."', ";
                    }
                        
                }
                $classe=substr($classe, 0, -2);
                fwrite($myfile, $classe);
                $classe="where ".$cle_primaire."="."'\$".$cle_primaire."'";
                fwrite($myfile, $classe);
                $classe=" \");\n";
                fwrite($myfile, $classe);
                $classe="}\n";
                fwrite($myfile, $classe);
                $classe="public function effacer(\$".$cle_primaire.")\n{\n";
                fwrite($myfile, $classe);
                $classe="
                try
                    {
                    /* On se connecte à MySQL*/
                    \$bdd = new PDO('mysql:host=".$servername.";dbname=".$ddbase.";charset=utf8', '".$username."', '',array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
                    }catch(Exception \$e)
                    {
                    // En cas d'erreur, on affiche un message et on arrête tout
                    die('Erreur : '.\$e->getMessage());
                    } ";
                fwrite($myfile, $classe);
                
                $classe="\$suprimer = \$bdd->query(\"DELETE FROM ".$table." WHERE ".$cle_primaire."='\$".$cle_primaire."'\");";
                fwrite($myfile, $classe);
                $classe="\n}\n";
                fwrite($myfile, $classe);
                $classe="\n}\n";
                fwrite($myfile, $classe);
                unset($val);
            unset($classe);
            unset($valeur);
            unset($classe);
            }
            
                    
    } catch (Exception $e) { echo $e->getMessage(); }
    $conn = null; //deconnection
    $classe="?> ";
    fwrite($myfile, $classe);
    fclose($myfile);
}

//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//+++++++++++++++++++++++++++++++++++++FIN CREATION DE L'API CRUD++++++++++++++++++++++++++
}
?> 