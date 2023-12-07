<?php

declare(strict_types=1);

use App\Event;

return function (\App\CallableEventDispatcher $dispatcher)
{

	$dispatcher->addListener(Event\Radio\WriteLiquidsoapConfiguration::class, function(Event\Radio\WriteLiquidsoapConfiguration $event) {

		// Get original LS config.
		$liq_config = $event->buildConfiguration();	

		// Get replacement sets.
		$plugin_dir = __DIR__;
		$dirs = glob("$plugin_dir/liq/*", GLOB_ONLYDIR);
		// Sort by name.
		natsort($dirs);
				
		// Iterate over replacement sets.
		$header_comments = "";
		foreach ($dirs as $dir) {
			
			$find_file = "$dir/find.liq";
			$replace_file = "$dir/replace.liq";
			$injectbefore_file = "$dir/inject.before.liq";
			$injectafter_file = "$dir/inject.after.liq";
			$dir_basename = basename($dir);
			
			$header_comments .= "# - $dir_basename\n";
					
			// Check whether find pattern exists.
			if (file_exists($find_file)) {
				
				$find_str = file_get_contents($find_file);
				$replace_liq = file_exists($replace_file) ? file_get_contents($replace_file) : null;
				$injectbefore_liq = file_exists($injectbefore_file) ? file_get_contents($injectbefore_file) : null;
				$injectafter_liq = file_exists($injectafter_file) ? file_get_contents($injectafter_file) : null;
				
				// Replace find pattern logic.
				if ($replace_liq) {
					$replace_str = "\n\n# START REPLACE: $dir_basename\n$replace_liq\n# END REPLACE: $dir_basename\n\n";
				}
				// Inject before find pattern logic.
				elseif ($injectbefore_liq) {
					$replace_str = "\n\n# START INJECT-BEFORE: $dir_basename\n$injectbefore_liq\n# END INJECT-BEFORE: $dir_basename\n\n$find_str";
				}
				// Inject after find pattern logic.
				elseif ($injectafter_liq) {
					$replace_str = "$find_str\n\n# START INJECT-AFTER: $dir_basename\n$injectafter_liq\n# END INJECT-AFTER: $dir_basename\n\n";
				}
				// Shouldn't happen. Make sure nothing unexpected can happen.
				else {
					$find_str = "";
					$replace_str = "";
				}

				// Execute string replacement.
				$liq_config = str_ireplace($find_str,$replace_str,$liq_config);

			}
			
		}
		
		// Add header comments
		$liq_config = "# THIS SCRIPT WAS CHANGED BY LS-CONFIG-REPLACE PLUGIN\n$header_comments\n$liq_config";
		// Beautify new config code
		$liq_config = preg_replace("/\n{2,}/", "\n\n", $liq_config);
		
		// Set and return configLines property.
		$config_array = explode("\n", $liq_config);
		$reflection = new \ReflectionClass($event);
		$property = $reflection->getProperty('configLines');
		$property->setAccessible(true);
		return $property->setValue($event, $config_array);

	});

};

?>