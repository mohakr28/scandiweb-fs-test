<?php

namespace App\Controller;

use GraphQL\GraphQL as GraphQLBase;
use GraphQL\Error\DebugFlag;
use App\GraphQL\Schema;
use Throwable;

class GraphQLController
{
    public function handle(): string
    {
        // Debug flags can be controlled by environment variables in a real app.
        $debug = DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE;
        // $debug = DebugFlag::NONE; // For production

        try {
            $schema = Schema::get(); // Get the schema instance

            $rawInput = file_get_contents('php://input');
            if ($rawInput === false) {
                throw new \RuntimeException('Failed to get php://input');
            }

            $input = json_decode($rawInput, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Invalid JSON input: ' . json_last_error_msg());
            }
            
            $query = $input['query'] ?? null;
            $variableValues = $input['variables'] ?? null;
            $operationName = $input['operationName'] ?? null;

            if (empty($query)) {
                throw new \RuntimeException('GraphQL query is missing.');
            }

            // The root value is not typically used if resolvers fetch their own data.
            $rootValue = []; 
            $result = GraphQLBase::executeQuery($schema, $query, $rootValue, null, $variableValues, $operationName);
            $output = $result->toArray($debug);

        } catch (Throwable $e) {
            // Log the full error internally.
            error_log("GraphQL Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            $output = [
                'errors' => [ // GraphQL spec expects an 'errors' array.
                    [
                        'message' => $e->getMessage(),
                    ],
                ],
            ];
            // Consider setting an appropriate HTTP status code for errors.
            // http_response_code(500); 
        }

        // The Content-Type header is set in public/index.php
        return json_encode($output);
    }
}