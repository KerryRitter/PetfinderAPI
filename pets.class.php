<?php

/**
 * Container class with an array of pets.
 *
 * This object contains an array of pets, filterable by species and breed.
 *
 * @author Kerry Ritter
 */
class Pets {
    public $pets = array();
    
    /**
     * 
     * Adds all the pets in the parsed XML into the array.
     *
     * @param object $xml_array This is the XML for Pets that has been parsed into an object.
     * @return void
     */
    public function __construct($xml_array) {
        foreach ($xml_array as $pet_data) {
            $pet = new Pet($pet_data);
            $this->add_pet($pet);
        }        
    }
    
    /**
     * 
     * Adds a Pet object to the array.
     *
     * @param Pet $pet  This is the Pet object to be added.
     * @return void
     */
    function add_pet($pet) {
        array_push($this->pets, $pet);
    }
    
    /**
     * 
     * Returns an array of Pet objects with the requested species and breed.
     *
     * @param string $species  Case sensitive string. See http://api.petfinder.com/schemas/0.9/petfinder.xsd for options.
     * @return array(Pet)
     */
    function get_pets_by_species($species) {
        $filtered_pets = array();
        foreach ($this->pets as $pet) {
            if ($pet->animal == $species) {
                array_push($filtered_pets, $pet);
            }
        }
        return $filtered_pets;
    }
    
    /**
     * 
     * Returns an array of Pet objects with the requested species and breed.
     *
     * @param string $species  Case sensitive string. See http://api.petfinder.com/schemas/0.9/petfinder.xsd for options.
     * @param string $new_name  Case sensitive string. See http://api.petfinder.com/schemas/0.9/petfinder.xsd for options.
     * @return array(Pet)
     */
    function get_pets_by_species_and_breed($species, $breed) {
        $filtered_pets = array();
        foreach ($this->pets as $pet) {
            if ($pet->animal == $species && in_array($breed, $pet->breeds)) {
                array_push($filtered_pets, $pet);
            }
        }
        return $filtered_pets;
    }
}
