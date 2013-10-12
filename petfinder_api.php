<?php
require("pet.class.php");
require("pets.class.php");

/**
 * Register your application here, http://www.petfinder.com/developers/api-key, to get
 * your API key and secret.
*/
define("BASE_URL", "http://api.petfinder.com/");
define("API_KEY", "__YOUR_API_KEY___");
define("API_SECRET", "__YOUR_API_SECRET___");

/**
 * Contains all the functionality to interact with Petfinder's API.
 *
 * This object contains all data for a pet. It is constructed using XML that has been parsed into an object/array.
 *
 * @author Kerry Ritter
 */
class PetfinderAPI {
    public $token = null;
    
    /**
     * 
     * Creates the API object and obtains a token.
     * 
     */
    public function __construct() {
        $values = $this->api_call("auth.getToken", null);
        $this->token = (string) $values->auth->token;
    }
    
    /**
     * 
     * This creates the Pet based on the parsed XML data.
     * 
     * The key and token are automatically added into the request, and the required
     * signature is automatically generated.
     *
     * @param string $method    This is the API method you wish to use.
     * @param array $data   Key-value pair array of data involved in the request.
     * @return string   This is the URL with the needed query string.
     */
    public function construct_url($method, $data) {
        if ($data == null) { $data = array(); }
        $query_string = "?key=" . API_KEY . ($this->token == null ? "" : "&token=" . $this->token);
        foreach ($data as $key => $value) {
            $query_string .= "&". $key . "=" . $value;
        }
        $signature_unhashed = API_SECRET . str_replace("?", "", $query_string);
        $query_string .= "&sig=" . md5($signature_unhashed); 
        
        $endpoint = BASE_URL . $method . $query_string;
        return $endpoint;
    }
    
    /**
     * 
     * Makes a GET request with the endpoint assembled by construct_url()
     *
     * @param string $endpoint    The URL assembled by construct_url.
     * @return string   The unparsed XML string returned from the API.
     */
    function make_get_request($endpoint) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $endpoint,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/535.6 (KHTML, like Gecko) Chrome/16.0.897.0 Safari/535.6'
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    
    /**
     * 
     * Parses the XML string into a PHP object, SimpleXMLElement.
     *
     * @param string $xml   This is the raw XML string returned from the GET request to the API.
     * @return SimpleXMLElement The object created from the XML.
     */
    function parse_xml($xml) {        
        return simplexml_load_string($xml);
    }
    
    /**
     * 
     * Given the API method and the key-value pair array of data, it generates the necessary URI
     * and makes the GET request. The data is then transformed into an object.
     *
     * @param string $method    This is the API method you wish to use.
     * @param array $data   Key-value pair array of data involved in the request.
     * @return SimpleXMLElement The object created from the XML.
     */
    function api_call($method, $data) {
        $endpoint = $this->construct_url($method, $data);
        $xml_response = $this->make_get_request($endpoint);
        return $this->parse_xml($xml_response);
    }
    
    /**
     * 
     * Gets the information about a shelter based on the requested ID.
     *
     * @param string $id    The ID of the requested shelter.
     * @return SimpleXMLElement The object created from the XML.
     */
    function get_shelter($id) {
        return $this->api_call("shelter.get", array("id" => $id));
    }
    
    /**
     * 
     * Gets the all of the pets a shelter has posted to PetFinder.
     *
     * @param string $id    The ID of the requested shelter.
     * @param char $status  The request status of the pets (A, H, P, X)
     * @param string $page    (optional) If you are paginating results, this is the requested page.
     * @param int $count (optional) The number of pets to return.
     * @param string $species    (optional) Returns a filtered array with just pets of the requested species.
     * @param string $breed    (optional) Returns a filtered array with just pets of the requested breed.
     * @return SimpleXMLElement The object created from the XML.
     */
    function get_shelters_pets($id, $status = null, $page = 1, $count = 25, $species = null, $breed = null) {
        $page = $page - 1;
        
        $arguments = array("id" => $id, "count" => $count, "offset" => $count * $page);
        
        if ($status != null) {
            $arguments["status"] = $status;
        }
        
        $all_adoptable = new Pets($this->api_call("shelter.getPets", $arguments)->pets->pet);
        if ($species != null && $breed == null) {
            return $all_adoptable->get_pets_by_species($species);
        }
        else if ($species != null && $breed != null) {
            return $all_adoptable->get_pets_by_species_and_breed($species, $breed);
        }
        
        return $all_adoptable;
    }
    
    /**
     * 
     * Gets the all of the adoptable pets that a shelter has posted to PetFinder.
     *
     * @param string $id    The ID of the requested shelter.
     * @param string $page    (optional) If you are paginating results, this is the requested page.
     * @param int $count (optional) The number of pets to return.
     * @param string $species    (optional) Returns a filtered array with just pets of the requested species.
     * @param string $breed    (optional) Returns a filtered array with just pets of the requested breed.
     * @return SimpleXMLElement The object created from the XML.
     */
    function get_shelters_adoptable_pets($id, $page = 1, $count = 25, $species = null, $breed = null) {
        return $this->get_shelters_pets($id, "A", $page, $count, $species, $breed);
    }
    
    /**
     * 
     * Gets the all of the adopted or removed pets that a shelter has posted to PetFinder.
     *
     * @param string $id    The ID of the requested shelter.
     * @param string $page    (optional) If you are paginating results, this is the requested page.
     * @param int $count (optional) The number of pets to return.
     * @param string $species    (optional) Returns a filtered array with just pets of the requested species.
     * @param string $breed    (optional) Returns a filtered array with just pets of the requested breed.
     * @return SimpleXMLElement The object created from the XML.
     */
    function get_shelters_adopted_removed_pets($id, $page = 1, $count = 25, $species = null, $breed = null) {
        return $this->get_shelters_pets($id, "X", $page, $count, $species, $breed);
    }
    
    /**
     * 
     * Gets all of the pets pending adoption that a shelter has posted to PetFinder.
     *
     * @param string $id    The ID of the requested shelter.
     * @param string $page    (optional) If you are paginating results, this is the requested page.
     * @param int $count (optional) The number of pets to return.
     * @param string $species    (optional) Returns a filtered array with just pets of the requested species.
     * @param string $breed    (optional) Returns a filtered array with just pets of the requested breed.
     * @return SimpleXMLElement The object created from the XML.
     */
    function get_shelters_pending_pets($id, $page = 1, $count = 25, $species = null, $breed = null) {
        return $this->get_shelters_pets($id, "P", $page, $count, $species, $breed);
    }
    
    /**
     * 
     * Gets all of the pets on hold that a shelter has posted to PetFinder.
     *
     * @param string $id    The ID of the requested shelter.
     * @param string $page    (optional) If you are paginating results, this is the requested page.
     * @param int $count (optional) The number of pets to return.
     * @param string $species    (optional) Returns a filtered array with just pets of the requested species.
     * @param string $breed    (optional) Returns a filtered array with just pets of the requested breed.
     * @return SimpleXMLElement The object created from the XML.
     */
    function get_shelters_on_hold_pets($id, $page = 1, $count = 25, $species = null, $breed = null) {
        return $this->get_shelters_pets($id, "H", $page, $count, $species, $breed);
    }
}