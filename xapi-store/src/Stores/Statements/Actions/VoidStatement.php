<?php

namespace Trax\XapiStore\Stores\Statements\Actions;

use Trax\XapiStore\Exceptions\XapiBadRequestException;

trait VoidStatement
{
    /**
     * Void a statement.
     *
     * @param  string  $id
     * @return void
     *
     * @throws \Trax\XapiStore\Exceptions\XapiBadRequestException
     */
    public function voidStatement(string $id)
    {
        // Get the statement.
        $statement = $this->addFilter([
            'voided' => false,
            'uuid' => $id
        ])->get()->last();

        // No error when the statement is not found.
        if (!$statement) {
            return false;
        }
        
        // Voiding statement can not be voided.
        if ($statement->data->verb->id == 'http://adlnet.gov/expapi/verbs/voided') {
            throw new XapiBadRequestException('Voiding statement can not be voided.');
        }
        
        // Void it.
        $this->updateModel($statement, ['voided' => true]);
    }
}
