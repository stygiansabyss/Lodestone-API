<?php

	/*	Parser
	 *	------
	 *	> getSource - $URL [protected] (Fetches the source code of the specified url.)
	 *	> curl - $URL [private] (Core curl function with additional options.)
	 */
	class Loadstone_Parser
	{
		// The source code of the most recent curl
		protected $SourceCodeArray;

		// Find data based on a tag
		protected function find($Tag, $Clean = TRUE)
		{
			// Search for element
			foreach($this->SourceCodeArray as $Line)
			{
				// Trim line
				$Line = trim($Line);

				// Search line
				if(stripos($Line, $Tag) !== false)
				{
					// If clean, clean it!
					if ($Clean) { $Line = $this->Clean(strip_tags(html_entity_decode($Line))); }

					// If empty, return true for "found", else return line.
					if (empty($Line))
						return true;
					else
						return $Line;
				}
			}

			// No find
			return false;
		}

		// Find data based on a tag, and take the next i amount
		protected function findRange($Tag, $Range, $Tag2 = NULL, $Clean = TRUE)
		{
			$Found 		= false;
			$Found2		= false;
			$Interates 	= 0;
			$Array 		= NULL;

			// If range null
			if (!$Range) { $Range = 9999; }

			// Search for element
			foreach($this->SourceCodeArray as $Line)
			{
				// Trim line
				$Line = trim($Line);

				// Search line, mark found
				if(stripos($Line, $Tag) !== false) { $Found = true; }
				if(stripos($Line, $Tag2) !== false) { $Found2 = true; }

				if ($Found)
				{
					// If clean true, clean line!
					if ($Clean) { $Array[] = $this->Clean(strip_tags(html_entity_decode($Line))); } else { $Array[] = $Line; }

					// Iterate
					$Interates++;

					// If iterate hits range, break.
					if ($Interates == $Range  || $Found2) { break; }
				}
			}

			// Remove empty values
			$Array = isset($Array) ? array_values(array_filter($Array)) : NULL;

			// Return array, else false.
			if ($Array)
				return $Array;
			else
				return false;
		}

		// Finds all entries based on a tag, and take the next i amount
		protected function findAll($Tag, $Range, $Tag2 = NULL, $Clean = TRUE)
		{
			$Found 		= false;
			$Found2		= false;
			$Interates 	= 0;
			$Array 		= NULL;
			$Array2		= NULL;

			// If range null
			if (!$Range) { $Range = 9999; }

			// Search for element
			foreach($this->SourceCodeArray as $Line)
			{
				// Trim line
				$Line = trim($Line);

				// Search line, mark found
				if(stripos($Line, $Tag) !== false && $Tag) { $Found = true; }
				if(stripos($Line, $Tag2) !== false && $Tag2) { $Found2 = true; }

				if ($Found)
				{
					// If clean true, clean line!
					if ($Clean) { $Array[] = $this->Clean(strip_tags(html_entity_decode($Line))); } else { $Array[] = $Line; }

					// Iterate
					$Interates++;

					// If iterate hits range, append to array and null.
					if ($Interates == $Range || $Found2)
					{
						// Remove empty values
						$Array = array_values(array_filter($Array));

						// Append
						$Array2[] = $Array;
						$Array = NULL;

						// Reset founds
						$Found 		= false;
						$Found2 	= false;
						$Interates 	= 0;
					}
				}
			}

			// Return array, else false.
			if ($Array2)
				return $Array2;
			else
				return false;
		}

		// Removes section of array up to specified tag
		protected function segment($Tag)
		{
			// Loop through source code array
			$i = 0;
			foreach($this->SourceCodeArray as $Line)
			{
				// If find tag, break
				if(stripos($Line, $Tag) !== false) { break; }
				$i++;
			}

			// Splice array
			array_splice($this->SourceCodeArray, 0, $i);
		}

		// Clean a found results
		private function clean($Line)
		{
			// Strip tags
			$Line = strip_tags(html_entity_decode($Line));

			// Random removals
			$Remove = array("-->");
			$Line = str_ireplace($Remove, NULL, $Line);

			// Return value
			return $Line;
		}

		// Prints the source array
		public function printSourceArray()
		{
			Show($this->SourceCodeArray);
		}

		// Get the DOMDocument from the source via its URL.
		protected function getSource($URL)
		{
			// Get the source of the url
			# Show($URL);
			$Source = $this->curl($URL);
			$this->SourceCodeArray = explode("\n", $Source);
			return true;
		}

		// Fetches page source via CURL
		private function curl($URL)
		{
			$options = array(
				CURLOPT_RETURNTRANSFER	=> true,         	// return web page
				CURLOPT_HEADER         	=> false,        	// return headers
				CURLOPT_FOLLOWLOCATION 	=> false,        	// follow redirects
				CURLOPT_ENCODING       	=> "",     			// handle all encodings
				CURLOPT_AUTOREFERER    	=> true,         	// set referer on redirect
				CURLOPT_CONNECTTIMEOUT 	=> 15,           	// timeout on connects
				CURLOPT_TIMEOUT        	=> 15,           	// timeout on response
				CURLOPT_MAXREDIRS      	=> 5,            	// stop after 10 redirects
				CURLOPT_USERAGENT      	=> "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.110 Safari/537.36",
				CURLOPT_HTTPHEADER     	=> array('Content-type: text/html; charset=utf-8', 'Accept-Language: en'),
			);

			$ch = curl_init($URL);
			curl_setopt_array($ch, $options);
			$source = curl_exec($ch);
			curl_close($ch);
			return htmlentities($source);
		}
	}