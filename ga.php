<?php

$winningGenome = 5828090;//null; //Winning number, or null to have a random one chosen between 0 and $maxRandom
$mutationChance = 10; //Higher the number, the less random mutations will occur.
$maxRandom = 9999999; //Max: 99999999999
$minFitness = 70; //Fitness scores higher than (worse) this number will be automatically pruned from the breeding pool.
$population = 10; //Genome Population per Generation
$generationLimit = 30000; //Limit the generations to run the GA against.


/* Known Bugs
 * - The win comparison is numeric... e.g. 012345 is counted as a match if the genome is 12345... Type casting to string should work? It doesn't. Shucks.
 * - Do the initial parents contribute to the first generation's children? Doesn't look like they do.
 */



// Initialize sequences, parents, and other information.
if ($winningGenome==null) {
	$winningGenome = mt_rand(0,$maxRandom);
}
if ($parentOne==null) {
	$parentOne = mt_rand(0,$maxRandom);	
}
if ($parentTwo==null) {
	$parentTwo = mt_rand(0,$maxRandom);	
}

//benchmarking
function microtime_float() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

$generation = 0;
$genome = '';

echo "============================\n";
echo "= Genetic Algorithm v0.0.1 =\n";
echo "============================\n";
echo "=   (c)2015 Robert Lerner  =\n";
echo "=   www.robert-lerner.com  =\n";
echo "============================\n";
echo "\nIdeal Genome: $winningGenome\n";
echo "Seed Parents: [1] $parentOne -- [2] $parentTwo\n";
sleep(5);

$time_start = microtime_float();
while ($genome!=$winningGenome) {
	$generation++;

	echo " -----------------------------------------------------------\n";
	echo " | Population |   Genome    | Fitness | Mutated | Dispositon\n";
	echo " -----------------------------------------------------------\n";

	//Generate population size desired for this generation.
	for ($seq=1;$seq<=$population;$seq++) {
 	
 		// == Evolution and Mutation ==
 		// ParentOne is typically the most fit, or sometimes shares fitness level with Parent Two.
 		// Because of this, the length of ParentOne is considered to be a more-optimal length
 		// than that of other previous children. If two numbers in a child's sequence match
 		// in the same location (e.g. 12345 92999), the matching numbers are kept, and the remainder
 		// evolved. In the example above, the resultant pattern would be ?2???. Each of the question
 		// marks indicates an evolution point, where a random number (0-9) will be inserted for the
 		// childen of the generation they parent. Additionally, a mutation can occur (set by mutationChance)
 		// where the ?2??? number "2" can also be discarded. While this can hinder the evolutionary growth,
 		// it helps stifle stale generations that repeat patterns with no real gains. Finally, the last
 		// mutation point exists for the length of the string. Using mutationChance, there is a 2/3 chance
 		// of the mutationChance that the string length will be modified, and a 50% chance of either it being
 		// reduced by 1 character, or having a random (0-9) character appended to it.
 		$genome = '';
 		$mutated = false;
		$leadParentSize = strlen($parentOne);
	 	for ($i=0;$i<$leadParentSize;$i++) {
	 		if (substr($parentOne,$i,1)==substr($parentTwo,$i,1)) {
	 			if (mt_rand(0,$mutationChance)==0) { //Handle evolution with programmed mutation rate.
	 				$genome .= mt_rand(0,9);
	 				$mutated = true;
	 			}
	 			else {
	 				$genome .= substr($parentOne,$i,1);	
	 			}
	 			
	 		}
	 		else {
	 			$genome .= mt_rand(0,9);
	 		}
	 	}
		
		if (mt_rand(0,$mutationChance)==0) { //Mutation of length
			$offset = mt_rand(-1,1); //If zero, mutation is ignored. Effectively 2/3's the mutation rate for string length.
			if ($offset==-1) {
				if (strlen($genome)>1) { //Make sure the genome is at least 2 characters before evolving to a shorter genome.
					$genome = substr($genome,0,strlen($genome)+$offset);
					$mutated = true;
				}
			}
			elseif ($offset==1) {
				$genome .= mt_rand(0,9);
				$mutated = true;
			}
		}


		// == Determination of Fitness ==
		// The higher the resolution, and the more conditionals, the better the fitness function will perform, and
		// the sooner the GA will find a match.
		//
		// Since this GA only compares numeric strings, we can use similar_text to derive a percentage of
		// text similarity as a baseline. Additional weight is added for the levenshtein distance, however since
		// this function yields a very low resolution, it does not present a good indicatior of fitness for a partucular
		// environment. Finally, a string length comparison is done with the winning sequence. If it does not match, a
		// 25% penalty weight is added to the final score as well.
		// In this algorithm, the lower the fitness score, the more performant the genome.
		//
		// Finally, a string length distance check is performed, and a fitness-scaled penalty is added, the greater the
		// difference is string size, the larger the penalty.
	 	similar_text($genome,$winningGenome,$fitness);
	 	$fitness = 100-$fitness; //Inverse for sorting. Levenshtein returns lower numbers when better, so do that here too.
	 	$fitness += levenshtein($genome,$winningGenome);
	 	if (strlen($genome)!=strlen($winningGenome)) {
	 		// $fitness += ($fitness*abs((strlen($genome)-strlen($winningGenome))*.50)); //Scaled, reduced performance.
	 		$fitness += abs(strlen($genome)-strlen($winningGenome))*20; //Linear
	 	}


		echo " | " . str_pad($seq,10,' ') . " | " . str_pad($genome,11,' ') . " | " . str_pad(number_format($fitness,2),7,' ') . " | " . str_pad(($mutated?'Yes':'No'),7,' ') . " | ";


	 	// == Genome Natural Selection ==
	 	// Twins should not be able to mate, since that would introduce a stale pattern into the mix, and
	 	// cause several generations to be wasted waiting for a mutation to occur. Therefore, they are
	 	// killed off (removed) from the mating pool upon birth.
	 	//
	 	// Additionally, a minimum fitness level can be prescribed, in which unfit parents can be killed off
	 	// before reproducing. This, in theory, should result in a better match. In the event that there is only
	 	// one parent remaining, a random parent will be introduced. If there are no parents remaining (the entire
	 	// generation was killed off), then two parents will be generated. This is the same as restarting the GA,
	 	// except the generation counter continues to be iterated.
	 	//
	 	// Killed off children are still presented in the console, however they will not exist in the $collection
	 	// mating pool.
	 	if ($fitness<=$minFitness) {
		 	//Retain genome vs. fitness to determine parents, if genome isn't a twin
		 	if (isset($collection[$genome])) {
		 		echo " (Killed, twin)\n";
		 	}
		 	else {
 				$collection[$genome] = $fitness;
 				echo "\n";
 			}
	 	}
	 	else {
	 		echo " (Killed, < min fitness)\n";
	 	}
	}

	// == Parental Selection and Mating Pool Continuity ==
	// The $collection mating pool is sorted (after removing twins/min fitness),
	// and the two most fit children become the next generation's parents.
	//
 	// There are going to be situations where one or both parent are missing. This condition 
 	// results from the natural selection code above killing off genomes. If that is the case, 
 	// regenerate parents at random.
 	asort($collection);
 	$parentOne = each($collection);
 	$parentOne = ($parentOne[0]==''?mt_rand(0,$maxRandom):$parentOne[0]);
 	$parentTwo = each($collection);
 	$parentTwo = ($parentTwo[0]==''?mt_rand(0,$maxRandom):$parentTwo[0]);



 	$collection = [];

 	// == Detect Win Condition ==
 	// If either of the parents match the winning genome, perform benchmark and return success.
 	if ((string) $parentOne== (string)$winningGenome || (string) $parentTwo== (string) $winningGenome) {
		$time = bcsub(microtime_float(),$time_start,5);
		$genomeTime = bcdiv($time,$generation*$population,5);
 		die("Generation $generation has created the optimal genome: $winningGenome. Average: $genomeTime seconds.\n");
 	}



 	//Get next child pattern for display.
 	$pattern = '';
	$leadParentSize = strlen($parentOne);
 	for ($i=0;$i<$leadParentSize;$i++) {
 		if (substr($parentOne,$i,1)==substr($parentTwo,$i,1)) {
 				$pattern .= substr($parentOne,$i,1);	
 			}
 		else {
 			$pattern .= '-';
 		}
 	}

 	echo "-------- Generation $generation Synopsis--------\n";
 	echo "Parent1: $parentOne\n";
 	echo "Parent2: $parentTwo\n";
 	echo "Child:   $pattern\n";

 	if ($generation>=$generationLimit) {
 		die("\n[!] Generation Limit Hit at " . number_format($generationLimit) . " generations. Quitting.");
 	}


 	// This delay is so the console can be watched. Disabling it will yield super fast results,
 	// but no voyeristic pleasure.
 	usleep(175000);

}
