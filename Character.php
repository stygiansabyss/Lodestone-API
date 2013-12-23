<?php

	/*	Character
	 *	---------
	 */
	class Lodestone_Character extends Lodestone_API
	{
		private $ID;
		private $Lodestone;
		private $Name;
		private $NameClean;
		private $Server;
		private $Avatars;
		private $Portrait;
		private $Legacy;
		private $Race;
		private $Clan;
		private $Nameday;
		private $Guardian;
		private $Company;
		private $FreeCompany;
		private $City;
		private $Biography;
		private $Stats;
		private $Gear;
		private $Minions;
		private $Mounts;
		private $ClassJob;
		private $Validated = true;
		private $Errors = array();
		
		#-------------------------------------------#
		# FUNCTIONS									#
		#-------------------------------------------#
		
		// ID
		public function setID($ID, $URL = NULL)
		{
			$this->ID = $ID;
			$this->Lodestone = $URL;
		}
		public function getID() { return $this->ID; }
		public function getLodestone() { return $this->Lodestone; }
		
		// NAME + SERVER
		public function setNameServer($String)
		{
			$Data = str_ireplace(")", "", explode("(", (trim($String[0]))));
			$this->Name 	= htmlspecialchars_decode(trim($Data[0]), ENT_QUOTES);
			$this->Server 	= htmlspecialchars_decode(trim($Data[1]), ENT_QUOTES);
			$this->NameClean= preg_replace('/[^a-z]/i', '', strtolower($this->Name));	
		}
		public function getName() { return $this->Name; }
		public function getServer() { return $this->Server; }
		public function getNameClean() { return$this->NameClean; }
		
		// AVATAR
		public function setAvatar($String)
		{
			$String = $String[2];
			if (isset($String))
			{
				$this->Avatars['50'] = trim(explode('&quot;', $String)[1]);
				$this->Avatars['64'] = str_ireplace("50x50", "64x64", $this->Avatars['50']);
				$this->Avatars['96'] = str_ireplace("50x50", "96x96", $this->Avatars['50']);
			}
		}
		public function getAvatar($Size) { return $this->Avatars[$Size]; }
		
		// PORTRAIT
		public function setPortrait($String)
		{
			if (isset($String))
			{
				$this->Portrait = trim(explode('&quot;', $String[1])[1]);
			}
		}
		public function getPortrait() { return $this->Portrait; }
		
		// RACE + CLAN
		public function setRaceClan($String)
		{
			if (isset($String))
			{
				$String 		= explode("/", $String);
				$this->Clan 	= htmlspecialchars_decode(trim($String[1]), ENT_QUOTES);
				$this->Race 	= htmlspecialchars_decode(trim($String[0]), ENT_QUOTES);
			}
		}
		public function getRace() { return $this->Race; }
		public function getClan() { return $this->Clan; }
		
		// LEGACY
		public function setLegacy($String) { $this->Legacy = $String; }
		public function getLegacy() { return $this->Legacy; }
		
		// BIRTHDATE + GUARDIAN + COMPANY + FREE COMPANY
		public function setBirthGuardianCompany($String)
		{
			$this->Nameday 		= trim(strip_tags(html_entity_decode($String[11])));
			$this->Guardian 	= str_ireplace("&#39;", "'", trim(strip_tags(html_entity_decode($String[15]))));
				
			$i = 0;
			foreach($String as $Line)
			{
				if (stripos($Line, 'Grand Company') !== false) 	{ $Company = trim(strip_tags(html_entity_decode($String[($i + 1)]))); }
				if (stripos($Line, 'Free Company') !== false) 	{ $FreeCompany = trim($String[($i + 1)]); }
				$i++;
			}
			
			// If grand company
			if (isset($Company))
			{
				$this->Company 		= array("name" => explode("/", $Company)[0], "rank" => explode("/", $Company )[1]);
			}
			
			// If free company
			if (isset($FreeCompany))
			{
				$FreeCompanyID		= trim(filter_var(explode('&quot;', $FreeCompany)[1], FILTER_SANITIZE_NUMBER_INT));
				$this->FreeCompany 	= array("name" => trim(strip_tags(html_entity_decode($FreeCompany))), "id" => $FreeCompanyID);
			}
		}
		public function getNameday() 		{ return $this->Nameday; }
		public function getGuardian() 		{ return $this->Guardian; }
		public function getCompanyName() 	{ return $this->Company['name']; }
		public function getCompanyRank() 	{ return $this->Company['rank']; }
		public function getFreeCompany() 	{ return $this->FreeCompany; }
		
		// CITY
		public function setCity($String) { $this->City = htmlspecialchars_decode(trim($String[1]), ENT_QUOTES); }
		public function getCity() { return $this->City; }
		
		// BIOGRAPHY
		public function setBiography($String) { $this->Biography = trim($String[0]); }
		public function getBiography() { return $this->Biography; }
		
		// HP + MP + TP
		public function setHPMPTP($String) 
		{ 
			$this->Stats['core']['hp'] = trim($String[0]);
			$this->Stats['core']['mp'] = trim($String[1]);
			$this->Stats['core']['tp'] = trim($String[2]);
		}
		
		// ATTRIBUTES
		public function setAttributes($String) 
		{ 
			$this->Stats['attributes']['strength'] 		= trim($String[0]);
			$this->Stats['attributes']['dexterity'] 	= trim($String[1]);
			$this->Stats['attributes']['vitality'] 		= trim($String[2]);
			$this->Stats['attributes']['intelligence'] 	= trim($String[3]);
			$this->Stats['attributes']['mind'] 			= trim($String[4]);
			$this->Stats['attributes']['piety'] 		= trim($String[5]);
		}
		
		// ELEMENTAL
		public function setElemental($String) 
		{ 
			$this->Stats['elemental']['fire'] 			= trim(filter_var($String[0], FILTER_SANITIZE_NUMBER_INT));
			$this->Stats['elemental']['ice'] 			= trim(filter_var($String[1], FILTER_SANITIZE_NUMBER_INT));
			$this->Stats['elemental']['wind'] 			= trim(filter_var($String[2], FILTER_SANITIZE_NUMBER_INT));
			$this->Stats['elemental']['earth'] 			= trim(filter_var($String[3], FILTER_SANITIZE_NUMBER_INT));
			$this->Stats['elemental']['lightning'] 		= trim(filter_var($String[4], FILTER_SANITIZE_NUMBER_INT));
			$this->Stats['elemental']['water'] 			= trim(filter_var($String[5], FILTER_SANITIZE_NUMBER_INT));
		}
		
		// STATS > OFFENSE
		public function setOffense($String)
		{
			$this->Stats['offense']['accuracy'] 			= trim(filter_var($String[0], FILTER_SANITIZE_NUMBER_INT));
			$this->Stats['offense']['critical hit rate'] 	= trim(filter_var($String[1], FILTER_SANITIZE_NUMBER_INT));
			$this->Stats['offense']['determination'] 		= trim(filter_var($String[2], FILTER_SANITIZE_NUMBER_INT));
		}
		
		// STATS > DEFENSE
		public function setDefense($String)
		{
			$this->Stats['defense']['defense'] 				= trim(filter_var($String[0], FILTER_SANITIZE_NUMBER_INT));
			$this->Stats['defense']['parry'] 				= trim(filter_var($String[1], FILTER_SANITIZE_NUMBER_INT));
			$this->Stats['defense']['magic defense'] 		= trim(filter_var($String[2], FILTER_SANITIZE_NUMBER_INT));
		}
		
		// STATS > PHYSICAL
		public function setPhysical($String)
		{
			$this->Stats['physical']['attack power'] 		= trim(filter_var($String[0], FILTER_SANITIZE_NUMBER_INT));
			$this->Stats['physical']['skill speed'] 			= trim(filter_var($String[1], FILTER_SANITIZE_NUMBER_INT));
		}
		
		// STATS > RESISTS
		public function setResists($String)
		{
			$this->Stats['resists']['slashing'] 			= trim(filter_var($String[0], FILTER_SANITIZE_NUMBER_INT));
			$this->Stats['resists']['piercing'] 			= trim(filter_var($String[1], FILTER_SANITIZE_NUMBER_INT));
			$this->Stats['resists']['blunt'] 				= trim(filter_var($String[2], FILTER_SANITIZE_NUMBER_INT));
		}
		
		// STATS > SPELL
		public function setSpell($String)
		{
			$this->Stats['spell']['attack magic potency'] 	= trim(filter_var($String[0], FILTER_SANITIZE_NUMBER_INT));
			$this->Stats['spell']['healing magic potency']	= trim(filter_var($String[1], FILTER_SANITIZE_NUMBER_INT));
			$this->Stats['spell']['spell speed'] 			= trim(filter_var($String[2], FILTER_SANITIZE_NUMBER_INT));
		}
		
		// STATS > CRAFTING
		public function setCrafting($String)
		{
			$this->Stats['crafting']['craftsmanship'] 	= trim(filter_var($String[0], FILTER_SANITIZE_NUMBER_INT));
			$this->Stats['crafting']['control']			= trim(filter_var($String[1], FILTER_SANITIZE_NUMBER_INT));
		}
		
		// STATS > CRAFTING
		public function setGathering($String)
		{
			$this->Stats['gathering']['gathering'] 	= trim(filter_var($String[0], FILTER_SANITIZE_NUMBER_INT));
			$this->Stats['gathering']['Perception']	= trim(filter_var($String[1], FILTER_SANITIZE_NUMBER_INT));
		}
		
		// STATS > PVP
		public function setPVP($String)
		{
			$this->Stats['pvp']['morale'] = trim(filter_var($String[0], FILTER_SANITIZE_NUMBER_INT));
		}
		
		// GET STAT FUNC
		public function getStat($Type, $Attribute) { if (isset($this->Stats[$Type])) { return $this->Stats[$Type][$Attribute]; } else { return 0; }}
		public function getStats() { return $this->Stats; }
		
		// ACTIVE CLASS + LEVEL
		public function setActiveClassLevel($String)
		{
			$this->Stats['active']['level'] = trim(filter_var($String[0], FILTER_SANITIZE_NUMBER_INT));
		}
		// ACTIVE CLASS + ICON
		public function setActiveClassIcon($String)
		{
			$this->Stats['active']['icon'] = trim(explode('&quot;', $String[0])[3]);
		}
		
		// GEAR
		public function setGear($Array)
		{
			$this->Gear['slots'] = count($Array);
			$GearArray = NULL;
			
			// Loop through gear equipped
			$Main = NULL;
			foreach($Array as $A)
			{
				// Temp array
				$Temp = array();
				
				// Loop through data
				$i = 0;
				foreach($A as $Line)
				{
					// Item Icon
					if (stripos($Line, 'socket_64') !== false) { $Data = trim(explode('&quot;', $A[$i + 1])[1]); $Temp['icon'] = $Data; }
					if (stripos($Line, 'item_name') !== false) { $Data = trim(str_ireplace(array('>', '"'), NULL, strip_tags(html_entity_decode($A[$i + 2])))); $Temp['name'] = htmlspecialchars_decode(trim($Data), ENT_QUOTES); }
					if (stripos($Line, 'item_name') !== false) { 
						$Data = htmlspecialchars_decode(trim(html_entity_decode($A[$i + 3])), ENT_QUOTES);
						if (
							strpos($Data, " Arm") !== false || 
							strpos($Data, " Grimoire") !== false || 
							strpos($Data, " Tool") !== false
						) 
						{ $Main = $Data; $Data = 'Main'; }
						$Temp['slot'] = strtolower($Data);
					}
					
					// Increment
					$i++;
				}

				// Slot manipulation
				$Slot = $Temp['slot'];
				if (isset($GearArray['slots'][$Slot])) { $Slot = $Slot . 2; }	
				$Temp['slot'] = $Slot;	
				
				// Append array
				$GearArray['numbers'][] = $Temp;
				$GearArray['slots'][$Slot] = $Temp;
			}	
			
			// Set Gear
			$this->Gear['equipped'] = $GearArray;
			
			// Set Active Class
			$classjob = str_ireplace(array('Two-Handed ', 'One-Handed '), NULL, explode("'", $Main)[0]);
			$this->Stats['active']['class'] = $classjob;
			if (isset($this->Gear['equipped']['slots']['soul crystal'])) { $this->Stats['active']['job'] = str_ireplace("Soul of the ", NULL, $this->Gear['equipped']['slots']['soul crystal']); }
		}
		public function getGear()			{ return $this->Gear; }
		public function getEquipped($Type)	{ return $this->Gear['equipped'][$Type]; }
		public function getSlot($Slot)		{ return $this->Gear['equipped']['slots'][$Slot]; }
		public function getActiveClass() 	{ return $this->Stats['active']['class']; }
		public function getActiveJob() 		{ return isset($this->Stats['active']['job']) ? $this->Stats['active']['job'] : NULL; }
		public function getActiveLevel() 	{ return $this->Stats['active']['level']; }
		public function getActiveIcon() 	{ return $this->Stats['active']['icon']; }
		
		// MINIONS
		public function setMinions($Array)
		{
			// Pet array
			$Pets = array();
			
			// Loop through array
			$i = 0;
			foreach($Array as $A)
			{
				if (stripos($A, 'ic_reflection_box') !== false)
				{
					$arr = array();
					$arr['name'] = trim(explode('&quot;', $Array[$i])[5]);
					$arr['icon'] = trim(explode('&quot;', $Array[$i + 2])[1]);
					$Pets[] = $arr;
				}
				
				// Increment
				$i++;		
			}
			
			// set pets
			$this->Minions = $Pets;
		}
		public function getMinions() { return $this->Minions; }
		
		// MOUNTS
		public function setMounts($Array)
		{
			// Mount array
			$Mounts = array();
			
			// Loop through array
			$i = 0;
			foreach($Array as $A)
			{
				if (stripos($A, 'ic_reflection_box') !== false)
				{
					$arr = array();
					$arr['name'] = trim(explode('&quot;', $Array[$i])[5]);
					$arr['icon'] = trim(explode('&quot;', $Array[$i + 2])[1]);
					$Mounts[] = $arr;
				}
				
				// Increment
				$i++;		
			}
			
			// set Mounts
			$this->Mounts = $Mounts;
		}
		public function getMounts() { return $this->Mounts; }
		
		// CLASS + JOB
		public function setClassJob($Array)
		{
			// Temp array
			$Temp = array();
			
			// Loop through class jobs
			$i = 0;
			foreach($Array as $A)
			{
				// If class
				if(stripos($A, 'ic_class_wh24_box') !== false)
				{
					$Icon 	= isset(explode(" ", $A)[2]) ? explode('?', str_ireplace(array('"', 'src='), '', html_entity_decode(explode(" ", $A)[2])))[0] : null;
					$Class 	= strtolower(trim(strip_tags(html_entity_decode($Array[$i]))));
					$Level 	= trim(strip_tags(html_entity_decode($Array[$i + 1])));
					$EXP 	= trim(strip_tags(html_entity_decode($Array[$i + 2])));
					if ($Class)
					{
						$arr = array(
							'class' => $Class,
							'icon'	=> $Icon,
							'level' => $Level,
							'exp'	=> array(
								'current' => explode(" / ", $EXP)[0], 
								'max' => explode(" / ", $EXP)[1]
							)
						);
							
						$Temp[] = $arr;
						$Temp[$Class] = $arr;
					}
				}
				
				// Increment
				$i++;
			}
			
			$this->ClassJob = $Temp;
		}
		public function getClassJob($Class) { return $this->ClassJob[strtolower($Class)]; }
		public function getClassJobs($Specific = null) 
		{ 
			$arr = array();
			if ($Specific)
			{
				foreach($this->getClassJobs() as $Key => $Data)
				{
					if ($Specific == 'numbered')
					{
						if (is_numeric($Key)) 
						{
							$arr[] = $Data;
						}
					}
					else if ($Specific == 'named')
					{
						if (!is_numeric($Key)) 
						{
							$arr[$Key] = $Data;
						}
					}
				}
			}
			else
			{
				$arr = $this->ClassJob;
			}

			return $arr;
		}
		public function getClassJobsOrdered($Ascending = false, $Specific = NULL)
		{
			$ClassJobs = $this->getClassJobs();
			if ($Specific) { $ClassJobs = $this->getClassJobs($Specific); }
			$this->sksort($ClassJobs, "level", $Ascending);
			return $ClassJobs;
		}
		
		// VALIDATE
		public function validate()
		{
			// Check Name
			if (!$this->Name) 			{ $this->Validated = false; $this->Errors[] = 'Name is false'; }
			if (!$this->Server) 		{ $this->Validated = false; $this->Errors[] = 'Server is false'; }
			if (!$this->ID) 			{ $this->Validated = false; $this->Errors[] = 'ID is false'; }
			if (!$this->Lodestone) 		{ $this->Validated = false; $this->Errors[] = 'Lodestone URL is false'; }
			if (!$this->Avatars['96']) 	{ $this->Validated = false; $this->Errors[] = 'Avatars is false'; }
			
			if (!$this->Portrait) 		{ $this->Validated = false; $this->Errors[] = 'Portrait is false'; }
			if (!$this->Race) 			{ $this->Validated = false; $this->Errors[] = 'Race is false'; }
			if (!$this->Clan) 			{ $this->Validated = false; $this->Errors[] = 'Clan is false'; }
			if (!$this->Nameday) 		{ $this->Validated = false; $this->Errors[] = 'Nameday is false'; }
			if (!$this->Guardian) 		{ $this->Validated = false; $this->Errors[] = 'Guardian is false'; }
			if (!$this->City) 			{ $this->Validated = false; $this->Errors[] = 'City is false'; }
			
			if (!is_numeric($this->Stats['core']['hp'])) { $this->Validated = false; $this->Errors[] = 'hp is false or non numeric'; }
			if (!is_numeric($this->Stats['core']['mp'])) { $this->Validated = false; $this->Errors[] = 'mp is false or non numeric'; }
			if (!is_numeric($this->Stats['core']['tp'])) { $this->Validated = false; $this->Errors[] = 'tp is false or non numeric'; }
			
			foreach($this->ClassJob as $CJ)
			{
				if (!is_numeric($CJ['level']) && $CJ['level'] != '-') { $this->Validated = false; $this->Errors[] = $CJ['class'] .' level was non numeric and not "-"'; }
				if (!is_numeric($CJ['exp']['current']) && $CJ['exp']['current'] != '-') { $this->Validated = false; $this->Errors[] = $CJ['class'] .' level was non numeric and not "-"'; }
				if (!is_numeric($CJ['exp']['max']) && $CJ['exp']['current'] != '-') { $this->Validated = false; $this->Errors[] = $CJ['class'] .' level was non numeric and not "-"'; }
			}
		}
		public function isValid() { return $this->Validated; }
		public function getErrors() { return $this->Errors; }

	}