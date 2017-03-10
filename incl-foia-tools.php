<?php

	class abstractChunk {
		public $pos = 0;
		public $chunk = "";
	}
	function right($string, $length) {
   		return substr($string, -$length, $length);
	}

	function left($string, $length) {
		return substr($string, 0, $length);
	}

	function strip_punctuation($strIn) {
		return eregi_replace("[.,!?\"\*&#;:]","",$strIn);
	}
	function fdate($d) {
		$curDate = $d[year] . "-";
		if(strlen($d[mon]) == 1) {
			$curDate .= "0" . $d[mon];
		} else {
			$curDate .= $d[mon]; }
		$curDate .= "-";
		if(strlen($d[mday]) == 1) {
			$curDate .= "0" . $d[mday];
		} else {
			$curDate .= $d[mday]; }
		return $curDate;
	}

	function abstract2($strIn, $searchterm, $howmany) {
		// set style tags
		$matchStart = "<span style='font-weight:bold; background-color:yellow;'>";
		$matchEnd = "</span>";

		// process punctuation in both sets
		$howmany--;
		$searchterm = stripslashes(strip_tags($searchterm));
		$searchterm = eregi_replace("\"", "", $searchterm);
		$strIn = stripslashes(strip_tags($strIn));
		$strIn = eregi_replace("\"","",$strIn);
		$strIn = eregi_replace("\[","",$strIn);
		$strIn = eregi_replace("\]","",$strIn);
		$strIn = eregi_replace("\(", " ( ", $strIn);
		$strIn = eregi_replace("\)", " ) ", $strIn);
		$searchterm = eregi_replace(")","",$searchterm);
		$searchterm = eregi_replace("\(","",$searchterm);
		$searchterm = eregi_replace("\+","",$searchterm);
		$searchterm = explode(" ", $searchterm);
		$strIn = eregi_replace("\r", " ", $strIn);
		$strIn = eregi_replace("\. ", " . ", $strIn);
		$strIn = eregi_replace("\.\"", " .\" ", $strIn);
		$strIn = eregi_replace("\"", " \" ", $strIn);
		$strIn = eregi_replace(", ", " , ", $strIn);
		$words = explode(" ", $strIn);
		$w = count($words);

		//LOGICAL TEST
		//if any search word within the first 10, show the first 10
		$chunks = Array();
		$matchflagglobal = 0;
		for($i = 0; $i < $howmany; $i++) {
			$matchflag = 0;
			reset($searchterm);
			foreach($searchterm as $st) {
				$st_np = strip_punctuation($st);
				$st_np_l = strlen($st_np);
				if (strtoupper($st_np) == strtoupper(strip_punctuation($words[$i]))) {
					$matchflag = 1;
					$matchflagglobal = 1;
					$curAdd = "$matchStart$words[$i]$matchEnd";
				} elseif ((right($st,1) == "*") && (strtoupper($st_np) == strtoupper(left($words[$i],$st_np_l)))) {
					$matchflag = 1;
					$matchflagglobal = 1;
					$curAdd = "$matchStart$words[$i]$matchEnd";
				} else {
					if($matchflag <> 1) {
						$curAdd = $words[$i]; }
				}
			}
			$curOut .= $curAdd . " ";
		}
		$chunks[1] = new abstractChunk();
		$chunks[1]->chunk = $curOut;
		$chunks[1]->pos = 1;

		// what if there's no match in the first few words?
			// find the first instance of any searchterm
		$firstword = 999999999;

			$chowmany = $howmany;
			if($matchflagglobal == 1) {
				$chowmany = 7; }
//			foreach($searchterm as $st) {
				$st = $searchterm[0];
				$stw++;
				$ww = 0;
				foreach($words as $cw) {
					$ww++;
					if(strtoupper($cw) == strtoupper($st)) {
						if(($ww < $firstword) && ($ww > $howmany + 5)) {
							$firstword = $ww; }
					}
				}
//			}
			$curOut = "";
			$matchflagglobal2 = 0;
			for($i = $firstword - 5; $i < ($firstword- 5 + $howmany); $i++) {
				$curAdd = $words[$i];
				reset($searchterm);
				foreach($searchterm as $st) {
					$st_np = strip_punctuation($st);
					$st_np_l = strlen($st_np);
					if (strtoupper($st_np) == strtoupper(strip_punctuation($words[$i]))) {
						$matchflag = 1;
						$matchflagglobal2 = 1;
						$curAdd = "$matchStart$words[$i]$matchEnd";
					} elseif ((right($st,1) == "*") && (strtoupper($st_np) == strtoupper(left($words[$i],$st_np_l)))) {
						$matchflag = 1;
						$matchflagglobal2 = 1;
						$curAdd = "$matchStart$words[$i]$matchEnd";
					}
				}
				$curOut .= $curAdd . " ";
			}
			$chunks[2] = new abstractChunk();
			$chunks[2]->chunk = $curOut;
			$chunks[2]->pos = $firstword;

		//otherwise find the first instance of the first atom and put it into an object
		// then repreat with the second atom


		if(($matchflagglobal == 1) && ($matchflagglobal2 == 0)) {
			$strOut = $chunks[1]->chunk;
			if($w > $chowmany) {
				$strOut .= " ... "; }
		} elseif (($matchflagglobal == 0) && ($matchflagglobal2 == 1)) {
			$strOut = "... " . $chunks[2]->chunk;
		} else {
			$strOut = $chunks[1]->chunk . "&nbsp;&hellip;&nbsp;" . $chunks[2]->chunk;
		}

		$strOut = eregi_replace(" \. ", ". ", $strOut);
		$strOut = eregi_replace(" \.\" ", ".\"", $strOut);
		$strOut = eregi_replace(" , ", ", ", $strOut);
		$strOut = eregi_replace(" \" ", "\"", $strOut);
		$strOut = eregi_replace(" \( ", " (", $strOut);
		$strOut = eregi_replace(" \) ", ") ", $strOut);
		return $strOut;
	}


	// create search abstracts with search words boldfaced in results
	function k_abstract($strIn, $searchterm, $wc) {

		$matchStart = "<span style='font-weight:bold; background-color:yellow;'>";
		$matchEnd = "</span>";

		$searchterm = stripslashes(strip_tags($searchterm));
		$searchterm = eregi_replace("\"", "", $searchterm);
		$strIn = stripslashes(strip_tags($strIn));
		$strIn = eregi_replace("\"","",$strIn);
		$strIn = eregi_replace("\[","",$strIn);
		$strIn = eregi_replace("\]","",$strIn);
		$searchterm = eregi_replace(")","",$searchterm);
		$searchterm = eregi_replace("\(","",$searchterm);
		$searchterm = eregi_replace("\+","",$searchterm);
		$searchterm = explode(" ", $searchterm);
		$strIn = eregi_replace("\r", " ", $strIn);
		$strIn = eregi_replace("\. ", " . ", $strIn);
		$strIn = eregi_replace("\.\"", " .\" ", $strIn);
		$strIn = eregi_replace("\"", " \" ", $strIn);
		$strIn = eregi_replace(", ", " , ", $strIn);
		$words = explode(" ", $strIn);
		$w = count($words);
		$totwords = $w;
		if($w > $wc) {
			$w = $wc; }
		for($i = 0; $i < $w; $i++) {
			$curAdd = $words[$i];
			reset($searchterm);
			foreach ($searchterm as $stt) {
				$st_np = strip_punctuation($stt);
				$st_np_l = strlen($st_np);
				if (strtoupper($st_np) == strtoupper(strip_punctuation($words[$i]))) {
					$curAdd = "$matchStart$words[$i]$matchEnd";
				} elseif ((right($stt,1) == "*") && (strtoupper($st_np) == strtoupper(left($words[$i],$st_np_l)))) {
					$curAdd = "$matchStart$words[$i]$matchEnd";
				}
			}
			$curAdd = trim($curAdd);
			if(($curAdd == "<br>") || ($curAdd == "<li>") || ($curAdd == "<p>")) {
				$curAdd = ""; };
			$strOut .= $curAdd . " ";
		}
		$strOut = eregi_replace(" \. ", ". ", $strOut);
		$strOut = eregi_replace(" \.\" ", ".\"", $strOut);
		$strOut = eregi_replace(" , ", ", ", $strOut);
		$strOut = eregi_replace(" \" ", "\"", $strOut);
		if($totwords > $wc) {
			$strOut .= " ..."; }
		return $strOut;
	}

	function parse_search($strIn) {

		// figure out search atoms
			// blow parens into own terms
			// respect quoted phrases (maybe by re-assembling atoms?)
		$strIn = trim(stripslashes(strip_tags($strIn)));
		$strIn = eregi_replace(")", " ) ", $strIn);
		$strIn = eregi_replace("\(", " ( ", $strIn);
		$strIn = eregi_replace('\"', ' " ', $strIn);
		for($j = 1; $j <= 10; $j++) {
			$strIn = eregi_replace("  ", " ", $strIn); }
		$particles = explode(" ", $strIn);

		// reassemble particles into full search atoms by iterating
		$i = 0;
		$inquote = 0;
		$atoms = Array();
		foreach($particles as $particle) {
			if(($particle == "\"") && ($inquote == 0)) {
				$atoms[$i] .= $particle;
				$inquote = 1;
			} elseif (($particle == "\"") && ($inquote == 1)) {
				$atoms[$i] = substr($atoms[$i],0,strlen($atoms[$i])-1);
				$atoms[$i] .= $particle . " ";
				$inquote = 0;
				$i++;
			} else {
				$atoms[$i] .= $particle . " ";
				if(!$inquote) {
					$i++; }
			}
		}

		// iterate through atoms to apply modifiers
		$atom_modifiers = Array();
		$i = 0;
		for($j = 0; $j < count($atoms); $j++) {
			$atom_modifiers[$j] = "+"; }
		for($j = 0; $j < count($atoms); $j++) {
			// handle database-style modifiers
			if(preg_match("/[\+\-\~\><]/mi",$atoms[$j])) {
				$atom_modifiers[$j] = substr($atoms[$j],0,1);
				$atoms[$j] = peel_left($atoms[$j]);
			}

			// handle natural-language modifiers
			if(trim(strtoupper($atoms[$j])) == "OR") {
				$atom_modifiers[$j+1] = "";
				$atom_modifiers[$j-1] = "";
				$atom_modifiers[$j] = "";
				$atoms[$j] = "";
			} elseif (trim(strtoupper($atoms[$j])) == "AND") {
				$atoms[$j] = "";
				$atom_modifiers[$j] = "";
			} elseif (trim(strtoupper($atoms[$j])) == "NOT") {
				$atom_modifiers[$j+1] = "-";
				$atom_modifiers[$j] = "";
				$atoms[$j] = "";
			}
		}

		// iterate atoms and modifiers to build result string
		for($j = 0; $j < count($atoms); $j++) {
			if(preg_match("/[\(\)]/m",$atoms[$j])) {
				$strOut .= $atoms[$j] . " ";
			} elseif(trim($atoms[$j] <> " ")) {
				$strOut .= $atom_modifiers[$j] . $atoms[$j] . " ";
			}
		}
		$strOut = eregi_replace("  ", " ", $strOut);
		$strOut = eregi_replace("\( ", "+(", $strOut);
		$strOut = eregi_replace(" )", ")", $strOut);
		$strOut = addslashes($strOut);
		return $strOut;
	}


?>