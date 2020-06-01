<?php
/**
 * UserSignatureDefinition
 *
 * PHP version 5
 *
 * @category Class
 * @package  DocuSign\eSign
 * @author   Swaagger Codegen team
 * @link     https://github.com/swagger-api/swagger-codegen
 */

/**
 * DocuSign REST API
 *
 * The DocuSign REST API provides you with a powerful, convenient, and simple Web services API for interacting with DocuSign.
 *
 * OpenAPI spec version: v2
 * Contact: devcenter@docusign.com
 * Generated by: https://github.com/swagger-api/swagger-codegen.git
 *
 */

/**
 * NOTE: This class is auto generated by the swagger code generator program.
 * https://github.com/swagger-api/swagger-codegen
 * Do not edit the class manually.
 */

namespace DocuSign\eSign\Model;

use \ArrayAccess;

/**
 * UserSignatureDefinition Class Doc Comment
 *
 * @category    Class
 * @package     DocuSign\eSign
 * @author      Swagger Codegen team
 * @link        https://github.com/swagger-api/swagger-codegen
 */
class UserSignatureDefinition implements ArrayAccess
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      * @var string
      */
    protected static $swaggerModelName = 'userSignatureDefinition';

    /**
      * Array of property to type mappings. Used for (de)serialization
      * @var string[]
      */
    protected static $swaggerTypes = [
        'signature_font' => 'string',
        'signature_id' => 'string',
        'signature_initials' => 'string',
        'signature_name' => 'string'
    ];

    public static function swaggerTypes()
    {
        return self::$swaggerTypes;
    }

    /**
     * Array of attributes where the key is the local name, and the value is the original name
     * @var string[]
     */
    protected static $attributeMap = [
        'signature_font' => 'signatureFont',
        'signature_id' => 'signatureId',
        'signature_initials' => 'signatureInitials',
        'signature_name' => 'signatureName'
    ];


    /**
     * Array of attributes to setter functions (for deserialization of responses)
     * @var string[]
     */
    protected static $setters = [
        'signature_font' => 'setSignatureFont',
        'signature_id' => 'setSignatureId',
        'signature_initials' => 'setSignatureInitials',
        'signature_name' => 'setSignatureName'
    ];


    /**
     * Array of attributes to getter functions (for serialization of requests)
     * @var string[]
     */
    protected static $getters = [
        'signature_font' => 'getSignatureFont',
        'signature_id' => 'getSignatureId',
        'signature_initials' => 'getSignatureInitials',
        'signature_name' => 'getSignatureName'
    ];

    public static function attributeMap()
    {
        return self::$attributeMap;
    }

    public static function setters()
    {
        return self::$setters;
    }

    public static function getters()
    {
        return self::$getters;
    }

    

    

    /**
     * Associative array for storing property values
     * @var mixed[]
     */
    protected $container = [];

    /**
     * Constructor
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->container['signature_font'] = isset($data['signature_font']) ? $data['signature_font'] : null;
        $this->container['signature_id'] = isset($data['signature_id']) ? $data['signature_id'] : null;
        $this->container['signature_initials'] = isset($data['signature_initials']) ? $data['signature_initials'] : null;
        $this->container['signature_name'] = isset($data['signature_name']) ? $data['signature_name'] : null;
    }

    /**
     * show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalid_properties = [];
        return $invalid_properties;
    }

    /**
     * validate all the properties in the model
     * return true if all passed
     *
     * @return bool True if all properteis are valid
     */
    public function valid()
    {
        return true;
    }


    /**
     * Gets signature_font
     * @return string
     */
    public function getSignatureFont()
    {
        return $this->container['signature_font'];
    }

    /**
     * Sets signature_font
     * @param string $signature_font 
     * @return $this
     */
    public function setSignatureFont($signature_font)
    {
        $this->container['signature_font'] = $signature_font;

        return $this;
    }

    /**
     * Gets signature_id
     * @return string
     */
    public function getSignatureId()
    {
        return $this->container['signature_id'];
    }

    /**
     * Sets signature_id
     * @param string $signature_id Specifies the signature ID associated with the signature name. You can use the signature ID in the URI in place of the signature name, and the value stored in the `signatureName` property in the body is used. This allows the use of special characters (such as \"&\", \"<\", \">\") in a the signature name. Note that with each update to signatures, the returned signature ID might change, so the caller will need to trigger off the signature name to get the new signature ID.
     * @return $this
     */
    public function setSignatureId($signature_id)
    {
        $this->container['signature_id'] = $signature_id;

        return $this;
    }

    /**
     * Gets signature_initials
     * @return string
     */
    public function getSignatureInitials()
    {
        return $this->container['signature_initials'];
    }

    /**
     * Sets signature_initials
     * @param string $signature_initials 
     * @return $this
     */
    public function setSignatureInitials($signature_initials)
    {
        $this->container['signature_initials'] = $signature_initials;

        return $this;
    }

    /**
     * Gets signature_name
     * @return string
     */
    public function getSignatureName()
    {
        return $this->container['signature_name'];
    }

    /**
     * Sets signature_name
     * @param string $signature_name Specifies the user signature name.
     * @return $this
     */
    public function setSignatureName($signature_name)
    {
        $this->container['signature_name'] = $signature_name;

        return $this;
    }
    /**
     * Returns true if offset exists. False otherwise.
     * @param  integer $offset Offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    /**
     * Gets offset.
     * @param  integer $offset Offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    /**
     * Sets value based on offset.
     * @param  integer $offset Offset
     * @param  mixed   $value  Value to be set
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * Unsets offset.
     * @param  integer $offset Offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    /**
     * Gets the string presentation of the object
     * @return string
     */
    public function __toString()
    {
        if (defined('JSON_PRETTY_PRINT')) { // use JSON pretty print
            return json_encode(\DocuSign\eSign\ObjectSerializer::sanitizeForSerialization($this), JSON_PRETTY_PRINT);
        }

        return json_encode(\DocuSign\eSign\ObjectSerializer::sanitizeForSerialization($this));
    }
}


