<?php

/**
 * Contains all data for a pet.
 *
 * This object contains all data for a pet. It is constructed using XML that has been parsed into an object/array.
 *
 * @author Kerry Ritter
 */
class Pet {
    public $id = null;
    public $shelterId = null;
    public $shelterPetId = null;
    public $name = null;
    public $animal = null;
    public $breeds = null;
    public $mix = null;
    public $age = null;
    public $sex = null;
    public $size = null;
    public $options = null;
    public $description = null;
    public $lastUpdate = null;
    public $status = null;
    public $media = null;
    public $contact = null;
    
    /**
     * 
     * This creates the Pet based on the parsed XML data.
     *
     * @param object $xml_array This is the XML for Pet that has been parsed into an object.
     * @return void
     */
    public function __construct($xml_array) {
        $this->id = (string) $xml_array->id;
        $this->shelterId = (string) $xml_array->shelterId;
        $this->shelterPetId = $xml_array->shelterPetId->id;
        $this->name = (string) $xml_array->name;
        $this->animal = (string) $xml_array->animal;
        $this->mix = (string) $xml_array->mix;
        $this->age = (string) $xml_array->age;
        $this->sex = (string) $xml_array->sex;
        $this->size = (string) $xml_array->size;
        $this->description = (string) $xml_array->description;
        $this->lastUpdate = (string) $xml_array->lastUpdate;
        $this->status = (string) $xml_array->status;
        
        $this->contact = $xml_array->contact;        
        $this->options = $xml_array->options->option;
        
        $this->breeds = array();
        foreach ($xml_array->breeds as $breed) {
            array_push($this->breeds, (string) $breed->breed);
        }
        
        $this->media = array();
        foreach ($xml_array->media->photos->photo as $photo) {
            array_push($this->media, (string) $photo);
        }
    }
    
    
    /**
     * 
     * This function does some simple checking and returns the photo URL at the requested index.
     *
     * @param int $index    The array index of the requested photo URL.
     * @return void
     */
    public function get_photo($index) {
        $number_of_photos = count($this->media);
        if (($number_of_photos > 0) && ($number_of_photos >= $index)) {
            return $this->media[$index];
        }
        else { 
            return false;
        }
    }
}
