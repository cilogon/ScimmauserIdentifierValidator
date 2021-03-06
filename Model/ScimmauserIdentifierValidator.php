<?php

class ScimmauserIdentifierValidator extends AppModel {
  // Required by COmanage Plugins
  public $cmPluginType = "identifiervalidator";
  
  public $cmPluginInstantiate = false;
  
  // Document foreign keys
  public $cmPluginHasMany = array();
  
  // Association rules from this model to other models
  public $belongsTo = array("CoIdentifierValidator");
  
  public function cmPluginMenus() {
    return array();
  }
  
  /**
   * Validate the identifier against the configured block list.
   *
   * @param  String  $identifier            The identifier (or email address) to be validated
   * @param  Array   $coIdentifierValidator CO Identifier Validator configuration
   * @param  Array   $coExtendedType        CO Extended Type configuration describing $identifier
   * @param  Array   $pluginCfg             Configuration information for this plugin, if instantiated
   * @return Boolean True if $identifier is valid and available
   * @throws InvalidArgumentException If $identifier is not of the correct format
   * @throws OverflowException If $identifier is already in use
   */
  
  public function validate($identifier, $coIdentifierValidator, $coExtendedType, $pluginCfg) {

    $blockFilePath = Configure::read('ScimmauserIdentifierValidator.blockFilePath');

    $blockFile = fopen($blockFilePath, "r");
    if(!$blockFile) {
      throw new InvalidArgumentException("Error validating username. Please report this error to the administrators.");
    }

    $bad = false;

    while(!feof($blockFile)) {
      $badUsername = trim(fgets($blockFile));

      // Skip lines beginning with hash character.
      if(substr($badUsername, 0, 1) == "#") {
        continue;
      }

      // Skip empty lines.
      if(empty($badUsername)) {
        continue;
      }

      $comparison = strcasecmp($badUsername, $identifier);

      if($comparison == 0) {
        $bad = true;
        $this->log("Blocked use of identifier value $identifier");
        break;
      }

    }

    fclose($blockFile);

    if($bad) {
      throw new InvalidArgumentException("This username is not allowed");
    }
    
    // If we made it here we have no objection
    return true;
  }
}
