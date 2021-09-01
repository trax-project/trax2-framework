<?php

namespace Trax\XapiValidation\Parsing;

use Closure;
use Trax\Core\Validation;
use Trax\XapiValidation\Contracts\Schema;
use Trax\XapiValidation\Exceptions\XapiValidationException;

class Parser
{
    /**
     * Validation schema.
     *
     * @var \Trax\XapiValidation\Contracts\Schema
     */
    protected $schema;
    
    /**
     * Transform handler.
     *
     * @var \Closure
     */
    protected $transformHandler;
    
    /**
     * Constructor.
     *
     * @param  \Trax\XapiValidation\Contracts\Schema  $schema
     * @return void
     */
    public function __construct(Schema $schema)
    {
        $this->schema = $schema;
    }
    
    /**
     * Try to validate the object against the given schema property.
     *
     * @param  object|array  $object
     * @param  string  $schemaProp
     * @return array
     */
    public function validate($object, string $schemaProp): array
    {
        $this->transformHandler = null;
        return $this->errors($this->parseObject($object, $schemaProp));
    }

    /**
     * Transform the object given a transformer closure.
     *
     * @param  mixed  $object
     * @param  string  $schemaProp
     * @param  \Closure  $transform
     * @return mixed
     */
    public function transform($object, string $schemaProp, Closure $handler)
    {
        $this->transformHandler = $handler;
        $this->parseObject($object, $schemaProp);
        return $object;
    }

    /**
     * Try to validate the object with one of the given schema properties.
     *
     * @param  object|array  $object
     * @param  array  $schemaProps
     * @param  string  $path
     * @return array
     */
    protected function parseOr($object, array $schemaProps, string $path = ''): array
    {
        foreach ($schemaProps as $schemaProp) {
            if (empty($this->parseObject($object, $schemaProp, $path))) {
                return [];
            };
        }
        return [[$path => 'None of the following schemas were found: '.json_encode($schemaProps)]];
    }

    /**
     * Try to validate the object against the given schema property.
     *
     * @param  object|array  $object
     * @param  string  $schemaProp
     * @param  string  $path
     * @return array
     */
    protected function parseObject($object, string $schemaProp, string $path = ''): array
    {
        // This may happen when decoding JSON empty object.
        if (is_array($object) && empty($object)) {
            $object = (object)$object;
        }
        
        // Check that it is an object.
        if (!is_object($object)) {
            return [[$path => "[$schemaProp] must be an object."]];
        }

        $errors = [];

        // Validate with a schema method.
        $methodName = \Str::camel($schemaProp);
        if (method_exists($this->schema, $methodName)) {
            $errors = array_merge($errors, $this->schema->$methodName($object, $path));
        }
        
        // That's it, there is no static schema for this prop.
        if (!isset($this->schema->$schemaProp)) {
            return $errors;
        }

        // Try to define the schema from a prop.
        try {
            $schema = $this->objectSchema($object, $schemaProp, $path);
        } catch (XapiValidationException $e) {
            $errors = array_merge($errors, $e->errors());
            return $errors;
        }

        return $this->parseObjectWithSchema($object, $schema, $path, $errors);
    }

    /**
     * Try to validate the object against the given schema.
     *
     * @param  object|array  $object
     * @param  array  $schema
     * @param  string  $path
     * @param  array  $errors
     * @return array
     */
    protected function parseObjectWithSchema($object, array $schema, string $path = '', array $errors = []): array
    {
        $objectProps = get_object_vars($object);
        
        // Check that all required props are defined.
        foreach ($schema as $prop => $val) {
            if (in_array('required', $val) && !isset($objectProps[$prop])) {
                $errors[] = [$path => "The [$prop] property must be defined."];
            }
        }
        
        // Check all the object props.
        foreach ($objectProps as $name => $value) {
            //
            // Collect errors.
            $errors = array_merge(
                $errors,
                $this->parseObjectPropertyUnderSchema($object, $name, $value, $schema, "$path.$name")
            );
        }
        return $errors;
    }

    /**
     * Try to validate an object property under against the given schema.
     *
     * @param  object|array  $object
     * @param  string  $prop
     * @param  mixed  $value
     * @param  array  $schema
     * @param  string  $path
     * @param  array  $errors
     * @return array
     */
    protected function parseObjectPropertyUnderSchema($object, string $prop, $value, array $schema, string $path = ''): array
    {
        $errors = [];

        // Check that the prop is defined in the schema.
        if (!isset($schema[$prop])) {
            return [[$path => "This property is not allowed."]];
        }

        $propSchema = $schema[$prop];

        // $or.
        if (isset($propSchema['$or'])) {
            $found = false;
            foreach ($propSchema['$or'] as $schemaProp) {
                if (is_string($schemaProp)) {
                    // Validate an object.
                    if (empty($this->parseObject($value, $schemaProp, $path))) {
                        $found = true;
                        break;
                    }
                } else {
                    // Validate a list of objects.
                    if (empty($this->parseArray('object', $value, $schemaProp[0], $path))) {
                        $found = true;
                        break;
                    }
                }
            }
            if (!$found) {
                $schemas = json_encode($propSchema['$or']);
                $errors[] = [$path => "None of the following schemas are valid: $schemas"];
            }
        }
            
        // Format.
        if (isset($propSchema['format'])) {
            switch ($propSchema['format']) {
                case 'object':
                    $schemaProp = isset($propSchema['type']) ? $propSchema['type'] : $prop;
                    $errors = array_merge($errors, $this->parseObject($value, $schemaProp, $path));
                    break;
                case 'array':
                    $schemaProp = isset($propSchema['type']) ? $propSchema['type'] : null;
                    $errors = array_merge($errors, $this->parseArray($propSchema['items'], $value, $schemaProp, $path));
                    break;
                default:
                    $fixedValue = isset($propSchema['value']) ? $propSchema['value'] : null;
                    $errors = array_merge($errors, $this->parseValue($value, $propSchema['format'], $fixedValue, $path));
            }
        }

        // Apply transformation on value.
        if (isset($this->transformHandler)) {
            $handler = $this->transformHandler;
            $handler($object, $prop, $propSchema);
        }

        return $errors;
    }

    /**
     * Try to validate an array of objects with a given schema property.
     *
     * @param  string  $type
     * @param  mixed  $array
     * @param  string|null  $schemaProp
     * @param  string  $path
     * @return array
     */
    protected function parseArray(string $type, &$array, $schemaProp = null, string $path = ''): array
    {
        // Check that the given array is really an array.
        if (!is_array($array)) {
            return [[$path => 'This property must be an array.']];
        }

        // Check all the list items.
        $errors = [];
        foreach ($array as $index => $item) {
            if ($type == 'object') {
                $errors = array_merge($errors, $this->parseObject($item, $schemaProp, "$path.$index"));
            } else {
                $errors = array_merge($errors, $this->parseValue($item, $type, null, "$path.$index"));
            }
        }

        return $errors;
    }

    /**
     * Try to validate a value given its type which can be a set of rules.
     *
     * @param  mixed  $value
     * @param  string  $rules
     * @param  mixed  $fixedValue
     * @param  string  $path
     * @return array
     */
    protected function parseValue($value, $rules, $fixedValue = null, string $path = ''): array
    {
        // Check null value.
        if (is_null($value)) {
            return [[$path => "The value of this property can't be null."]];
        }
        
        // Check fixed value.
        if (isset($fixedValue) && $value !== $fixedValue) {
            return [[$path => "The value of this property must be [$fixedValue]."]];
        }
        
        // Check rules.
        if (!Validation::check($value, $rules)) {
            return [[$path => "This property is not valid against the rules [$rules]."]];
        }
        
        return [];
    }

    /**
     * Return the object schema given its schema property.
     *
     * @param  object  $object
     * @param  string  $schemaProp
     * @param  string  $path
     * @return  array
     */
    protected function objectSchema($object, string $schemaProp, string $path = ''): array
    {
        // Simple schema, without $extend.
        $schema = $this->schema->$schemaProp;
        if (!isset($schema['$extend'])) {
            return $schema;
        }
        
        // Extend with one of the schema props.
        if (isset($schema['$extend']['$or'])) {
            return $this->extendObjectSchemaWithOneOf($object, $schema, $schema['$extend']['$or'], $path);
        }

        // Extend with a given schema prop.
        return $this->extendObjectSchemaWith($object, $schema, $schema['$extend']['type'], $path);
    }

    /**
     * Extend the object schema with one of potentiel complementary schemas.
     *
     * @param  object  $object
     * @param  array  $schema
     * @param  string  $extension
     * @param  string  $path
     * @return  array
     */
    protected function extendObjectSchemaWith($object, array $schema, string $extension, string $path = ''): array
    {
        $extendedSchema = array_merge($schema, $this->schema->$extension);
        unset($extendedSchema['$extend']);

        // Simple extension, without a $choice prop.
        if (!isset($extendedSchema['$choice'])) {
            return $extendedSchema;
        }
        
        // Extension with a $choice prop. One of them is required.
        $requiredChoice = in_array('required', $schema['$extend']);
        return $this->extendObjectSchemaWithChoice($object, $extendedSchema, $extendedSchema['$choice'], $requiredChoice, $path);
    }

    /**
     * Extend the object schema with one of potentiel 'CHOICE' schemas.
     *
     * @param  object  $object
     * @param  array  $schema
     * @param  array  $candidates
     * @param  bool  $required
     * @param  string  $path
     * @return  array
     *
     * @throws \Trax\XapiValidation\Exceptions\XapiValidationException
     */
    protected function extendObjectSchemaWithChoice($object, array $schema, array $candidates, bool $required, string $path = ''): array
    {
        unset($schema['$choice']);

        foreach ($candidates as $choice => $choiceSchema) {
            //
            // Extend the schema with a candidate choice.
            $extendedSchema = array_merge($schema, [$choice => $choiceSchema]);

            // Make it required.
            if ($required) {
                $extendedSchema[$choice][] = 'required';
            }
            
            // Check the validity of the extended schema for this object.
            if (empty($this->parseObjectWithSchema($object, $extendedSchema))) {
                return $extendedSchema;
            }
            // Else, we continue with another candidate.
        }
        // No candidate matches.
        $props = json_encode(array_keys($candidates));
        $errors = [[$path => "None of the following properties are valid: $props"]];
        throw new XapiValidationException('xAPI Validation Error(s)', $object, $errors);
    }
    
    /**
     * Extend the object schema with one of potentiel 'OR' schemas.
     *
     * @param  object  $object
     * @param  array  $schema
     * @param  array  $candidateProps
     * @param  string  $path
     * @return  array
     *
     * @throws \Trax\XapiValidation\Exceptions\XapiValidationException
     */
    protected function extendObjectSchemaWithOneOf($object, array $schema, array $candidateProps, string $path = ''): array
    {
        unset($schema['$extend']);

        foreach ($candidateProps as $extension) {
            //
            // Extend the schema with a candidate extension.
            $extendedSchema = array_merge($schema, $this->schema->$extension);
            
            // Check the validity of the extended schema for this object.
            if (empty($this->parseObjectWithSchema($object, $extendedSchema))) {
                return $extendedSchema;
            }
            // Else, we continue with another candidate.
        }
        // No candidate matches.
        $props = json_encode($candidateProps);
        $errors = [[$path => "None of the following properties are valid: $props"]];
        throw new XapiValidationException('xAPI Validation Error(s)', $object, $errors);
    }

    /**
     * Finalize errors.
     *
     * @param  array  $errors
     * @return array
     */
    protected function errors(array $errors): array
    {
        $res = [];
        foreach ($errors as $error) {
            foreach ($error as $prop => $val) {
                break;
            }
            $prop = \Str::after($prop, '.');
            if (!isset($res[$prop])) {
                $res[$prop] = [];
            }
            $res[$prop][] = $val;
        }
        return $res;
    }
}
