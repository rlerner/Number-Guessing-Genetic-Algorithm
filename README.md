# Number-Guessing-Genetic-Algorithm
A useless PHP-CLI script to guess a provided number.


## Running the script
The script should run on anything that runs PHP, assuming you can run CLI scripts

Run script on the command line as follows, assuming PHP is in your $PATH / %PATH%:
`php ga.php`


## Changing how the program runs

The first few lines of the file contain a few parameters that can be configured:

 - $winningGenome - This is the number the genetic algorithm will determine as "most fit" or the winner.
 - $mutationChance - Uses Mersenne Twister algorithm, with zero being the minimum, and $mutationChance being the maximum to determine if a genome will mutate. For example, if you stick with 10, there's a 1 in 11 (0-10) chance that a genome will mutate, perhaps delaying the evolution, or speeding it up by flushing stale genomes.
 - $maxRandom - Limits the number guessing field in characters. If you want it to ever find number 123, then it should be at least three characters above this number to guess 123... typically 999.
 - $minFitness - Fitness scores worse (higher) than this number will be pruned from the breeding pool. Too low a number will be too specific, and may kill off genomes that could have evolved into the winning genome. Too high a number, and evolution will take a long time.
 - $population - How many genomes will be generated from the parents for each generation.
 - $generationLimit - If we hit this many generations, lets just let the species die. It isn't getting anywhere fast.
 

## Damn this thing runs slow
That's because I'm a bit of a voyeur. There are two sleep commands in here, one so my pretty splash text can show, the other is a loop delay. Comment them out to see it full speed, if you can even see anything at that rate.
