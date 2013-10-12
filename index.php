<?php
require("petfinder_api.php");

$api = new PetfinderAPI();
$pets = $api->get_shelters_adoptable_pets("IL158", 1, 25, "Dog", "Beagle");
var_dump($pets);