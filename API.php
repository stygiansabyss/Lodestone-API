<?php
	/*
		XIVPads.com (v4) - Lodestone Query API
		--------------------------------------------------
		Author: 	Josh Freeman (Premium Virtue)
		Support:	http://xivpads.com/?Portal
		Version:	4.0
		PHP:		5.4

		Always ensure you download from the github
		https://github.com/viion/XIVPads-LodestoneAPI
		--------------------------------------------------
	*/

	// Debug stuff
 	#error_reporting(-1);
	//function show($Data) { echo '<pre>'; print_r($Data); echo '</pre>'; }

	/*	LodestoneAPI
	 *	------------
	 */
	class Lodestone_API extends Loadstone_Parser
	{
		// url addresses to various lodestone content. (DO NOT CHANGE, it will break some functionality of the API)
		private $URL =
		[
			# Search related urls
			'search' =>
			[
				'query'			=> '?q=%name%&worldname=%server%',
			],

			# Character related urls
			'character' => 
			[	
				'profile'		=> 'http://eu.finalfantasyxiv.com/lodestone/character/',
				'achievement' 	=> '/achievement/kind/',
			],

			# Free company related urls
			'freecompany' => 
			[
				'profile'		=> 'http://eu.finalfantasyxiv.com/lodestone/freecompany/',
				'member' 		=> '/member/',
				'memberpage' 	=> '?page=%page%',
			],

			# Linkshell related urls
			'linkshell' =>
			[
				'profile'		=> 'http://eu.finalfantasyxiv.com/lodestone/linkshell/',
				'activity'		=> '/activity/',
			],
		];

		// defaults
		private $defaults =
		[
			'automaticallyParseFreeCompanyMembers' => false,
			'pagesPerFreeCompanyMemberList' => 20,
		];
		
		// Configuration
		public $AchievementCategories = [1, 2, 4, 5, 6, 8, 11, 12, 13];
		public $ClassList = [];
		public $ClassDisicpline = [];
		
		// List of characters parsed
		public $Characters = [];
		public $Achievements = [];
		public $Search = [];
		
		// List of free company data parsed
		public $FreeCompanyList = [];
		public $FreeCompanyMembersList = [];

		// List of linkshell data parsed
		public $Linkshells = [];
		
		public function __construct()
		{
			// Set classes
			$this->ClassList = array(
				"Gladiator", "Pugilist", "Marauder", "Lancer", "Archer", "Conjurer", "Thaumaturge", "Arcanist", "Carpenter", "Blacksmith", 
				"Armorer", "Goldsmith", "Leatherworker", "Weaver", "Alchemist", "Culinarian", "Miner", "Botanist", "Fisher"
			);
			
			// Set class by disicpline							
			$this->ClassDisicpline = array(
				"dow" => array_slice($this->ClassList, 0, 5),
				"dom" => array_slice($this->ClassList, 5, 3),
				"doh" => array_slice($this->ClassList, 8, 8),
				"dol" => array_slice($this->ClassList, 16, 3),
			);
		}
		
		// Quick get
		public function get($Array, $Options = null)
		{
			// Clean
			$Name 	= isset($Array['name']) 	? trim(ucwords($Array['name'])) : NULL;
			$Server = isset($Array['server']) 	? trim(ucwords($Array['server'])) : NULL;
			$ID		= isset($Array['id']) 		? trim($Array['id']) : NULL;
			
			// If no ID passed, find it.
			if (!$ID)
			{
				// Search by Name + Server, exact
				$this->searchCharacter($Name, $Server, true);
				
				// Get by specific ID
				$ID = $this->getSearch()['results'][0]['id'];
			}
			
			// If an ID
			if ($ID)
			{
				// Parse profile
				$this->parseProfile($ID);
				
				// Return character
				return $this->getCharacterByID($ID);
			}
			else
			{
				return false;
			}
		}

		// Quick get free company
		public function getFC($Array, $Options = null)
		{
			// Clean
			$Name 	= isset($Array['name']) 	? trim(ucwords($Array['name'])) : NULL;
			$Server = isset($Array['server']) 	? trim(ucwords($Array['server'])) : NULL;
			$ID		= isset($Array['id']) 		? trim($Array['id']) : NULL;

			// If no ID passed, find it.
			if (!$ID)
			{
				// Search by Name + Server, exact
				$this->searchFreeCompany($Name, $Server, true);
				
				// Get by specific ID
				$ID = $this->getSearch()['results'][0]['id'];
			}
			
			// If an ID
			if ($ID)
			{
				// Parse profile
				$this->parseFreeCompany($ID, $Options);
				
				// Return character
				return $this->getFreeCompanyByID($ID);
			}
			else
			{
				return false;
			}
		}

		// Quick get linkshell
		public function getLS($Array, $Options = null)
		{
			// Clean
			$Name 	= isset($Array['name']) 	? trim(ucwords($Array['name'])) : NULL;
			$Server = isset($Array['server']) 	? trim(ucwords($Array['server'])) : NULL;
			$ID		= isset($Array['id']) 		? trim($Array['id']) : NULL;

			// If no ID passed, find it.
			if (!$ID)
			{
				// Search by Name + Server, exact
				$this->searchLinkshell($Name, $Server, true);
				
				// Get by specific ID
				$ID = $this->getSearch()['results'][0]['id'];
			}
			
			// If an ID
			if ($ID)
			{
				// Parse profile
				$this->parseLinkshell($ID, $Options);
				
				// Return character
				return $this->getLinkshellByID($ID);
			}
			else
			{
				return false;
			}
		}

		#-------------------------------------------#
		# SEARCH									#
		#-------------------------------------------#

		// Search a character by its name and server.
		public function searchCharacter($Name, $Server, $GetExact = true)
		{
			if (!$Name)
			{
				echo "error: No Name Set.";	
			}
			else if (!$Server)
			{
				echo "error: No Server Set.";	
			}
			else
			{
				// Exact name for later
				$ExactName = $Name;

				// Get the source
				$this->getSource($this->URL['character']['profile'] . str_ireplace(array('%name%', '%server%'), array(str_ireplace(" ", "+", $Name), $Server), $this->URL['search']['query']));

				// Get all found characters
				$Found = $this->findAll('thumb_cont_black_50', 10, NULL, false);

				// Loop through results
				if ($Found)
				{
					foreach($Found as $F)
					{
						$Avatar 	= explode('&quot;', $F[1])[3];
						$Data 		= explode('&quot;', $F[6]);
						$ID			= trim(explode('/', $Data[3])[3]);
						$NameServer	= explode("(", trim(str_ireplace(">", NULL, strip_tags(html_entity_decode($Data[4]))))); 
						$Name		= htmlspecialchars_decode(trim($NameServer[0]), ENT_QUOTES);
						$Server		= trim(str_ireplace(")", NULL, $NameServer[1]));
						$Language 	= $F[4];
						
						// Append search results
						$this->Search['results'][] = array(
							"avatar" 	=> $Avatar,
							"name"		=> $Name,
							"server"	=> $Server,
							"id"		=> $ID,
						);
					}
					
					// If to get exact
					if ($GetExact)
					{
						$Exact = false;
						foreach($this->Search['results'] as $Character)
						{
							//show($Character['name'] .' < > '. $ExactName);
							//show(md5($Character['name']) .' < > '. md5($ExactName));
							//show(strlen($Character['name']) .' < > '. strlen($ExactName));
							if (($Character['name']) == ($ExactName) && strlen(trim($Character['name'])) == strlen(trim($ExactName)))
							{
								$Exact = true;
								$this->Search['results'] = NULL;
								$this->Search['results'][] = $Character;
								$this->Search['isExact'] = true;
								break;
							}
						}
						
						// If no exist false, null array
						if (!$Exact)
						{
							$this->Search = NULL;	
						}
					}
					
					// Number of results
					$this->Search['total'] = count($this->Search['results']);
				}
				else
				{
					$this->Search['total'] = 0;
					$this->Search['results'] = NULL;	
				}
			}
		}
		
		// Search a free company by name and server
		public function searchFreeCompany($Name, $Server, $GetExact = true)
		{
			if (!$Name)
			{
				echo "error: No Name Set.";	
			}
			else if (!$Server)
			{
				echo "error: No Server Set.";	
			}
			else
			{
				// Exact name for later
				$ExactName = $Name;

				// Get the source
				$this->getSource($this->URL['freecompany']['profile'] . str_ireplace(array('%name%', '%server%'), array(str_ireplace(" ", "+", $Name), $Server), $this->URL['search']['query']));

				// Get all found data
				$Found = $this->findAll('ic_freecompany_box', 20, NULL, false);
				
				// if found
				if ($Found)
				{
					foreach($Found as $F)
					{
						$Company 	= $this->clean($F[3]);
						$ID			= trim(explode("/", $F[5])[3]);
						$Name 		= trim(explode("(", $this->clean($F[5]))[0]);
						$Server 	= str_ireplace(")", "", trim(explode("(", $this->clean($F[5]))[1]));
						$Members 	= trim(explode(":", $this->clean($F[8]))[1]);
						$Formed 	= trim(explode(",", explode("(", $F[13])[2])[0]);

						$this->Search['results'][] = 
						array(
							"id"		=> $ID,
							"company" 	=> $Company,
							"name"		=> $Name,
							"server"	=> $Server,
							"members"	=> $Members,
							"formed"	=> $Formed,
						);
					}

					// If to get exact
					if ($GetExact)
					{
						$Exact = false;
						foreach($this->Search['results'] as $FreeCompany)
						{
							if (($FreeCompany['name']) == ($ExactName) && strlen(trim($FreeCompany['name'])) == strlen(trim($ExactName)))
							{
								$Exact = true;
								$this->Search['results'] = NULL;
								$this->Search['results'][] = $FreeCompany;
								$this->Search['isExact'] = true;
								break;
							}
						}
						
						// If no exist false, null array
						if (!$Exact)
						{
							$this->Search = NULL;	
						}
					}
				}
				else
				{
					$this->Search['total'] = 0;
					$this->Search['results'] = NULL;	
				}
	  		}
		}

		// Search a linkshell by name and server
		public function searchLinkshell($Name, $Server, $GetExact = true)
		{
			if (!$Name)
			{
				echo "error: No Name Set.";	
			}
			else if (!$Server)
			{
				echo "error: No Server Set.";	
			}
			else
			{
				// Exact name for later
				$ExactName = $Name;

				// Get the source
				$this->getSource($this->URL['linkshell']['profile'] . str_ireplace(array('%name%', '%server%'), array(str_ireplace(" ", "+", $Name), $Server), $this->URL['search']['query']));

				// Get all found data
				$Found = $this->findAll('player_name_gold linkshell_name', 5, NULL, false);
				
				// if found
				if ($Found)
				{
					foreach($Found as $F)
					{
						$ID 		= trim(explode("/", $F[0])[3]);
						$Name 		= trim(str_ireplace(['&quot;', '&lt;', '&gt;'], null, explode("/", $F[0])[4]));
						$Server 	= trim(strip_tags(html_entity_decode(str_ireplace(")", null, explode("(", $F[0])[1]))));
						$Members	= trim(explode(":", strip_tags(html_entity_decode($F[3])))[1]);

						$this->Search['results'][] = 
						[
							"id"		=> $ID,
							"name"		=> $Name,
							"server"	=> $Server,
							"members"	=> $Members,
						];
					}

					// If to get exact
					if ($GetExact)
					{
						$Exact = false;
						foreach($this->Search['results'] as $Linkshell)
						{
							if (($Linkshell['name']) == ($ExactName) && strlen(trim($Linkshell['name'])) == strlen(trim($ExactName)))
							{
								$Exact = true;
								$this->Search['results'] = NULL;
								$this->Search['results'][] = $Linkshell;
								$this->Search['isExact'] = true;
								break;
							}
						}
						
						// If no exist false, null array
						if (!$Exact)
						{
							$this->Search = NULL;	
						}
					}
				}
				else
				{
					$this->Search['total'] = 0;
					$this->Search['results'] = NULL;	
				}
	  		}
		}
		
		// Get search results
		public function getSearch() { return $this->Search; }

		// Checks if an error page exists
		public function errorPage($ID)
		{
			// Get the source
			$this->getSource($this->URL['character']['profile'] . $ID);

			// Check character tag
			$PageNotFound = $this->find('/lodestone/character/');
			
			// if not found, error.
			if (!$PageNotFound) { return true; }

			return false;
		}
		
		#-------------------------------------------#
		# PROFILE									#
		#-------------------------------------------#
		
		// Parse a profile based on ID.
		public function parseProfile($ID)
		{
			if (!$ID)
			{
				echo "error: No ID Set.";	
			}
			else if ($this->errorPage($ID))
			{
				echo "error: Character page does not exist.";	
			}
			else
			{
				// Get the source
				$this->getSource($this->URL['character']['profile'] . $ID);
				
				// Create a new character object
				$Character = new Lodestone_Character();
				
				// Set Character Data
				$Character->setID(trim($ID), $this->URL['character']['profile'] . $ID);
				$Character->setNameServer($this->findRange('player_name_brown', 3));

				// Only process if character name set
				if (strlen($Character->getName()) > 3)
				{
					$Character->setAvatar($this->findRange('thumb_cont_black_40', 3, NULL, false));
					$Character->setPortrait($this->findRange('bg_chara_264', 2, NULL, false));
					$Character->setRaceClan($this->find('chara_profile_title'));
					$Character->setLegacy($this->find('bt_legacy_history'));
					$Character->setBirthGuardianCompany($this->findRange('chara_profile_list', 60, NULL, false));
					$Character->setCity($this->findRange('City-state', 5));
					$Character->setBiography($this->findRange('txt_selfintroduction', 5));
					$Character->setHPMPTP($this->findRange('param_power_area', 10));
					$Character->setAttributes($this->findRange('param_list_attributes', 8));
					$Character->setElemental($this->findRange('param_list_elemental', 8));
					$Character->setOffense($this->findRange('param_title_offense', 6));
					$Character->setDefense($this->findRange('param_title_deffense', 6));
					$Character->setPhysical($this->findRange('param_title_melle', 6));
					$Character->setResists($this->findRange('param_title_melleresists', 6));
					$Character->setActiveClassLevel($this->findRange('&quot;class_info&quot;', 3));
					$Character->setActiveClassIcon($this->findRange('&quot;ic_class_wh24_box&quot;', 1, null, false));
					
					// Set Gear (Also sets Active Class and Job)
					$Gear = $this->findAll('item_detail_box', NULL, '//ITEM Detail', false);
					$Character->setGear($Gear);
					
					// The next few attributes are based on class
					if (in_array($Character->getActiveClass(), $this->ClassDisicpline['dow']) || in_array($Character->getActiveClass(), $this->ClassDisicpline['dom']))
					{
						$Character->setSpell($this->findRange('param_title_spell', 6));
						$Character->setPVP($this->findRange('param_title_pvpparam', 6));
					}
					else if (in_array($Character->getActiveClass(), $this->ClassDisicpline['doh']))
					{
						$Character->setCrafting($this->findRange('param_title_crafting', 6));
					}
					else if (in_array($Character->getActiveClass(), $this->ClassDisicpline['dol']))
					{
						$Character->setGathering($this->findRange('param_title_gathering', 6));
					}

					#$this->segment('area_header_w358_inner');
					
					// Set Minions
					$Minions = $this->findRange('area_header_w358_inner', NULL, '//Minion', false);
					$Character->setMinions($Minions);
					
					// Set Mounts
					$Mounts = $this->findRange('area_header_w358_inner', NULL, '//Mount', false, 2);
					$Character->setMounts($Mounts);
					
					#$this->segment('class_fighter');
					
					// Set ClassJob
					$Character->setClassJob($this->findRange('class_fighter', NULL, '//Class Contents', false));
					
					// Validate data
					$Character->validate();
					
					// Append character to array
					$this->Characters[$ID] = $Character;
				}
				else
				{
					$this->Characters[$ID] = NULL;
				}
			}
		}
		
		// Parse just biography, based on ID.
		public function parseBiography($ID)
		{
			// Get the source
			$this->getSource($this->URL['character']['profile'] . $ID);	
			
			// Create a new character object
			$Character = new Character();
			
			// Get biography
			$Character->setBiography($this->findRange('txt_selfintroduction', 5));
			
			// Return biography
			return $Character->getBiography();
		}
		
		// Get a list of parsed characters.
		public function getCharacters() { return $this->Characters;	}
		
		// Get a character by id
		public function getCharacterByID($ID) { return isset($this->Characters[$ID]) ? $this->Characters[$ID] : NULL; }
		
		#-------------------------------------------#
		# ACHIEVEMENTS								#
		#-------------------------------------------#
		
		// Get a list of parsed characters
		public function getAchievements() { return $this->Achievements; }
		
		// Parse a achievements based on ID
		public function parseAchievements()
		{
			$ID = $this->getID();

			if (!$ID)
			{
				echo "error: No ID Set.";	
			}
			else
			{
				// Loop through categories
				foreach($this->AchievementCategories as $Category)
				{
					// Get the source
					$x = $this->getSource($this->URL['character']['profile'] . $ID . $this->URL['character']['achievement'] .'category/'. $Category .'/');
					
					// Create a new character object
					$Achievements = new Achievements();
					
					// Get Achievements
					$Achievements->set($this->findAll('achievement_area_body', NULL, 'bt_more', false));
					$Achievements->setPoints($this->findRange('total_point', 10));
					$Achievements->setCategory($Category);
					
					// Append character to array
					$this->Achievements[$ID][$Category] = $Achievements;
				}
			}
		}

		// Parse achievement by category
		public function parseAchievementsByCategory($cID, $ID = null)
		{
			if (!$ID)
			{
				$ID = $this->getID();
			}

			if (!$ID)
			{
				echo "error: No ID Set.";	
			}
			else if (!$cID)
			{
				echo "No catagory id set.";
			}
			else
			{
				$Category = $this->AchievementCategories[$cID];

				// Get the source
				$this->getSource($this->URL['character']['profile'] . $ID . $this->URL['character']['achievement'] . $cID .'/');
				
				// Create a new character object
				$Achievements = new Achievements();
				
				// Get Achievements
				$Achievements->set($this->findAll('achievement_area_body', NULL, 'bt_more', false));
				$Achievements->setPoints($this->findRange('total_point', 10));
				$Achievements->setCategory($cID);
				
				// Append character to array
				$this->Achievements[$cID] = $Achievements;
			}
		}

		public function getAchievementCategories()
		{
			return $this->AchievementCategories;
		}

		#-------------------------------------------#
		# FREE COMPANY								#
		#-------------------------------------------#

		// Parse free company profile
		public function parseFreeCompany($ID, $Options = null)
		{
			if (!$ID)
			{
				echo "error: No ID Set.";	
			}
			else
			{
				// Options
				$this->defaults['automaticallyParseFreeCompanyMembers'] = (isset($Options['members'])) ? $Options['members'] : $this->defaults['automaticallyParseFreeCompanyMembers'];

				// Get source
				$this->getSource($this->URL['freecompany']['profile'] . $ID);

				// Create a new character object
				$FreeCompany = new Lodestone_FreeCompany();
				
				// Set Character Data
				$FreeCompany->setID(trim($ID), $this->URL['freecompany']['profile'] . $ID);
				$FreeCompany->setNameServerCompany($this->findRange('ic_freecompany_box', 10));
				$FreeCompany->setTagFormedMembersSlogan($this->findRange('table_black m0auto', 50, false, false));

				// If to parse free company members
				if ($this->defaults['automaticallyParseFreeCompanyMembers'])
				{
					// Temp array
					$MembersList = [];

					// Get number of pages
					$TotalPages = ceil(round(intval($FreeCompany->getMemberCount()) / intval(trim($this->defaults['pagesPerFreeCompanyMemberList'])), 10));

					// Get all members
					for($Page = 1; $Page <= $TotalPages; $Page++)
					{
						// Parse Members page
						$this->getSource($FreeCompany->getLodestone() . $this->URL['freecompany']['member'] . str_ireplace('%page%', $Page, $this->URL['freecompany']['memberpage']));

						// Set Members
						$MemberArray = $FreeCompany->parseMembers($this->findAll('player_name_area', 18, null, null));

						// Merge existing member list with new member array
						$MembersList = array_merge($MembersList, $MemberArray);
					}

					// End point for member list
					$FreeCompany->setMembers($MembersList);
				}

				// Save free company
				$this->FreeCompanies[$ID] = $FreeCompany;
			}
		}

		// Get a list of parsed free companies.
		public function getFreeCompanies() { return $this->FreeCompanies; }

		// Get a free company by id
		public function getFreeCompanyByID($ID) { return isset($this->FreeCompanies[$ID]) ? $this->FreeCompanies[$ID] : NULL; }

		#-------------------------------------------#
		# LINKSHELL									#
		#-------------------------------------------#

		// Parse free company profile
		public function parseLinkshell($ID, $Options = null)
		{
			if (!$ID)
			{
				echo "error: No ID Set.";	
			}
			else
			{
				// Get source
				$this->getSource($this->URL['linkshell']['profile'] . $ID);

				// Create a new character object
				$Linkshell = new Lodestone_Linkshell();
				
				// Set Character Data
				$Linkshell->setID(trim($ID), $this->URL['linkshell']['profile'] . $ID);
				$Linkshell->setNameServer($this->findRange('player_name_brown', 10));
				$Linkshell->setMemberCount($this->findRange('ic_silver', 5));
				$Linkshell->setMembers($this->findAll('thumb_cont_black_50', 40, false, false));


				// Save free company
				$this->Linkshells[$ID] = $Linkshell;
			}
		}

		// Get a list of parsed linkshells.
		public function getLinkshells() { return $this->Linkshells; }

		// Get a linkshell by id
		public function getLinkshellByID($ID) { return isset($this->Linkshells[$ID]) ? $this->Linkshells[$ID] : NULL; }

		#-------------------------------------------#
		# FUNCTIONS									#
		#-------------------------------------------#

		// This function will sort a multi dimentional array based on a key index, its global, do not use $var = sksort().
		protected function sksort(&$array, $subkey, $sort_ascending) 
		{
			if (count($array))
				$temp_array[key($array)] = array_shift($array);
			foreach($array as $key => $val){
				$offset = 0;
				$found = false;
				foreach($temp_array as $tmp_key => $tmp_val)
				{
					if(!$found and strtolower($val[$subkey]) > strtolower($tmp_val[$subkey]))
					{
						$temp_array = array_merge(    (array)array_slice($temp_array,0,$offset),
													array($key => $val),
													array_slice($temp_array,$offset)
												  );
						$found = true;
					}
					$offset++;
				}
				if(!$found) $temp_array = array_merge($temp_array, array($key => $val));
			}
			if ($sort_ascending)
				$array = array_reverse($temp_array);
			else 
				$array = $temp_array;
		}
	}