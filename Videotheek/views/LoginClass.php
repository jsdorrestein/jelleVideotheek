<?php
	require_once("MySqlDatabaseClass.php");
	require_once("MailingClass.php");


	class LoginClass
	{
		//Fields

		private $emailadres;
		private $wachtwoord;
		private $rol;
		private $geactiveerd;
		private $activatiedatum;
        private $naam;
        private $achternaam;
        private $adres;
        private $postcode;
        private $woonplaats;
        
        
		
		//Properties

		public function getEmailadres() { return $this->emailadres;}
		public function getWachtwoord() { return $this->wachtwoord; }
		public function getRol() { return $this->rol; }
		public function getGeactiveerd() { return $this->geactiveerd;}
		public function getActivatiedatum() { return $this->activatiedatum; }
        public function getNaam() { return $this->naam; }
        public function getAchternaam() { return $this->achternaam; }
        public function getAdres() { return $this->adres; }
        public function getPostcode() { return $this->postcode; }
        public function getWoonplaats() { return $this->woonplaats; }
     
		

		public function setEmailadres($value) { $this->emailadres = $value;}
		public function setWachtwoord($value) { $this->wachtwoord = value; }
		public function setRol($value) { $this->rol = $value; }
		public function setGeactiveerd($value) { $this->geactiveerd = $value;}
		public function setActivatiedatum($value) { $this->activatiedatum = value; }
        public function setNaam($value) { $this->setNaam = value; }
        public function setAchternaam($value) { $this->setAchternaam = value; }
        public function setAdres($value) { $this->setAdres = value; }
        public function setPostcode($value) { $this->setPostcode = value; }
        public function setWoonplaats($value) { $this->setWoonplaats = value; }

		
		
		//Constructor
		public function __construct() {}
		
		//Methods
		/* Hier komen de methods die de informatie in/uit de database stoppen/halen
		*/
		public static function find_by_sql($query)
		{
			// Maak het $database-object vindbaar binnen deze method
			global $database;
			
			// Vuur de query af op de database
			$result = $database->fire_query($query);
			
			// Maak een array aan waarin je LoginClass-objecten instopt
			$object_array = array();
			
			// Doorloop alle gevonden records uit de database
			while ( $row  = mysqli_fetch_array($result))
			{
				// Een object aan van de LoginClass (De class waarin we ons bevinden)
				$object = new LoginClass();
				
				// Stop de gevonden recordwaarden uit de database in de fields van een LoginClass-object
				$object->emailadres			= $row['emailadres'];
				$object->wachtwoord		= $row['wachtwoord'];
				$object->rol		= $row['rol'];
				$object->geactiveerd		= $row['geactiveerd'];
				$object->activatiedatum = $row['activatiedatum'];
                $object->naam = $row['naam'];
                $object->achternaam = $row['achternaam'];
                $object->adres = $row['adres'];
                $object->postcode = $row['postcode'];
                $object->woonplaats = $row['woonplaats'];
                $object->emailadres = $row['emailadres'];

			
				$object_array[] = $object;
			}
			return $object_array;
		}
		
		public static function find_login_by_email_password($emailadres, $wachtwoord)
		{
			$query = "SELECT *
					  FROM `gebruiker`
					  WHERE `emailadres` 	= '".$emailadres."'
					  AND	`wachtwoord`	= '".$wachtwoord."'";
					  
			$loginClassObjectArray = self::find_by_sql($query);
			$loginClassObject = array_shift($loginClassObjectArray);
			return $loginClassObject;
		}
		
		
		public static function insert_into_database($post)
		{
			global $database;
			
			date_default_timezone_set("Europe/Amsterdam");
			
			$datum = date('Y-m-d H:i:s');
			
			$wachtwoord = MD5($post['emailadres'].date('Y-m-d H:i:s'));
			
			$query = "INSERT INTO `gebruiker`  (                                    `naam`,
										   `emailadres`,
										   `wachtwoord`,
										   `rol`,
										   `geactiveerd`,
                                           `emailadres`,
                                           `adres`,
                                           `postcode`,
                                           `woonplaats`,
                                           `achternaam`)
					  VALUES			  (
                                           '".$_POST['naam']."',
										   '".$_POST['emailadres']."',
                                            '".$_POST['adres']."',
                                             '".$_POST['postcode']."',
                                              '".$_POST['woonplaats']."',
                                               '".$_POST['achternaam']."',
										   '".$wachtwoord."',
										   'klant',
										   'no'";
			// echo $query;
			$database->fire_query($query);
			
			$last_id = mysqli_insert_id($database->getDb_connection());
						
			self::send_email($last_id, $post, $wachtwoord);
						
			echo "Uw gegevens zijn verwerkt.";
			 header("refresh:3;url=home.html");		
		}
		
		public static function check_if_email_exists($emailadres)
		{
			global $database;
			
			$query = "SELECT `emailadres`
					  FROM	 `gebruiker`
					  WHERE	 `emailadres` = '".$emailadres."'";
					  
			$result = $database->fire_query($query);
			
			//ternary operator
			return (mysqli_num_rows($result) > 0) ? true : false;	
		}
		
				
		public static function check_if_email_password_exists($emailadres, $wachtwoord, $geactiveerd)
		{
			global $database;
			
			$query = "SELECT `emailadres`, `wachtwoord`, `geactiveerd`
					  FROM	 `gebruiker`
					  WHERE	 `emailadres` = '".$emailadres."'
					  AND	 `wachtwoord` = '".$wachtwoord."'";
					  
			$result = $database->fire_query($query);
			
			$record = mysqli_fetch_array($result);
			
			return (mysqli_num_rows($result) > 0 && $record['geactiveerd'] == $geactiveerd) ? true : false;	
		}
		
		public static function check_if_activated($emailadres, $wachtwoord)
		{
			global $database;
			
			$query = "SELECT `geactiveerd`
					  FROM	 `gebruiker`
					  WHERE	 `emailadres` = '".$emailadres."'
					  AND	 `wachtwoord` = '".$wachtwoord."'";
					  
			$result = $database->fire_query($query);			
			$record = mysqli_fetch_array($result);
			
			return ( $record['geactiveerd'] == 'no') ? true : false;
		}
				
		private static function send_email($idKlant, $post, $wachtwoord)
		{
			$to = $post['emailadres'];
			$subject = "Activatiemail Jelle Videotheek.";
			$message = "Geachte heer/mevrouw <b>".$post['naam']."</b><br>";
												
			$message .= '<style>a { color:red;}</style>';
			$message .= "Hartelijk dank voor het registreren op mijn videotheekwebsite."."<br>";
			$message .= "Uw registratienummer is: ".$idKlant."<br>";
			$message .= "U kunt de registratie voltooien door op de onderstaande"."<br>";
			$message .= "activatielink te klikken:"."<br>";
			
			$message .= "klik <a href='localhost/Project/index.php?content=activate&idKlant=".$idKlant."&emailadres=".$post['emailadres']."&wachtwoord=".$wachtwoord."'>hier</a> om uw account te activeren"."<br>";
			
			$message .= "U kunt dan vervolgens een nieuw wachtwoord instellen."."<br>";
			$message .= "Met vriendelijke groet,"."<br>";
			$message .= "Marielle van Dijk"."<br>";
		
			$headers = 'From: no-reply@project.nl'."\r\n";
			$headers .= 'Reply-To: info@project.nl'."\r\n";
			$headers .= 'Cc: admin@project.nl'."\r\n";
			$headers .= 'Bcc: accountant@project.nl'."\r\n";
			//$headers .= "MIME-version: 1.0"."\r\n";
			//$headers .= "Content-type: text/plain; charset=iso-8859-1"."\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1"."\r\n";		
			$headers .= 'X-Mailer: PHP/' . phpversion();
			
			
			mail( $to, $subject, $message, $headers); 
		}
		public static function activate_account_by_id($idKlant)
		{
			global $database;
			$query = "UPDATE `gebruiker`
					  SET `geactiveerd` = 'yes'
					  WHERE `naam` = '".$naam."'";
					  
			$database->fire_query($query);
			
		}
		
		public static function update_password($idKlant, $wachtwoord)
		{
			global $database;
			$query = "UPDATE `gebruiker` 
					  SET	 `wachtwoord` =	'".MD5($wachtwoord)."'
					  WHERE	 `naam`		=	'".$naam."'";
			$database->fire_query($query);
			echo "Uw wachtwoord is succesvol gewijzigd.";
			header("refresh:4;url=home.html?content=login_form");		
		}
		
		public static function check_old_password($oude_wachtwoord)
		{
			$query = "SELECT *
					  FROM	 `gebruiker`
					  WHERE	 `naam`	=	'".$_SESSION['naam']."'";
			$arrayLoginClassObjecten = self::find_by_sql($query);
			$loginClassObject = array_shift($arrayLoginClassObjecten);
			//var_dump($loginClassObject);
			//echo $loginClassObject->getPassword()."<br>";
			//echo MD5($old_password);
			if (!strcmp(MD5($oude_wachtwoord),$loginClassObject->getWachtwoord())) 
			{
				return true;			
			}
			else
			{
				return false;
			}		
		}	
	}
?>