<?php

namespace App\Catalog\Infrastructure\In\Http\Controllers;

use App\Catalog\Domain\UseCases\CreateItemIdentifier;
use App\Catalog\Domain\UseCases\FindItemByBarcode;
use App\Catalog\Domain\UseCases\FindItemByIdentifierValue;
use App\Catalog\Domain\ValueObjects\IdentifierType;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Thaumware\Support\Uuid\Uuid;

class ItemIdentifierController extends Controller
{
    public function findByBarcode(string $barcode, FindItemByBarcode $findItemByBarcode): JsonResponse
    {
        try {
            $result = $findItemByBarcode->execute($barcode);

            if (!$result) {
                return response()->json([
                    'error' => 'Item not found',
                    'message' => 'Barcode not found.',
                ], 404);
            }

            return response()->json([
                'data' => array_merge($result['item']->toArray(), [
                    'identifier' => $result['identifier']->toArray(),
                ]),
            ]);
        } catch (DomainException $exception) {
            return response()->json([
                'error' => 'Barcode conflict',
                'message' => $exception->getMessage(),
            ], 409);
        }
    }

    public function findByIdentifier(
        string $value,
        Request $request,
        FindItemByIdentifierValue $findItemByIdentifierValue
    ): JsonResponse {
        $types = $this->resolveLookupTypes($request->query('types'));

        try {
            $result = $findItemByIdentifierValue->execute(trim($value), $types);

            if (!$result) {
                return response()->json([
                    'error' => 'Item not found',
                    'message' => 'Identifier not found.',
                ], 404);
            }

            return response()->json([
                'data' => array_merge($result['item']->toArray(), [
                    'identifier' => $result['identifier']->toArray(),
                ]),
            ]);
        } catch (DomainException $exception) {
            return response()->json([
                'error' => 'Identifier conflict',
                'message' => $exception->getMessage(),
            ], 409);
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'error' => 'Invalid identifier lookup',
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    public function create(Request $request, string $id, CreateItemIdentifier $createItemIdentifier): JsonResponse
    {
        $validated = $request->validate([
            'type' => [
                'required',
                'string',
                Rule::in(array_map(
                    fn (IdentifierType $type) => $type->value,
                    IdentifierType::cases()
                )),
            ],
            'value' => 'required|string|max:255',
            'is_primary' => 'sometimes|boolean',
            'variant_id' => 'sometimes|nullable|string|uuid',
        ]);

        try {
            $identifier = $createItemIdentifier->execute(
                id: Uuid::v4(),
                itemId: $id,
                type: $validated['type'],
                value: trim($validated['value']),
                isPrimary: (bool) ($validated['is_primary'] ?? false),
                variantId: $validated['variant_id'] ?? null,
            );

            return response()->json([
                'data' => $identifier->toArray(),
            ], 201);
        } catch (InvalidArgumentException $exception) {
            $status = $exception->getMessage() === 'Item not found.' ? 404 : 409;

            return response()->json([
                'error' => 'Unable to create identifier',
                'message' => $exception->getMessage(),
            ], $status);
        }
    }

    /**
     * @return string[]
     */
    private function resolveLookupTypes(mixed $types): array
    {
        if (!is_string($types) || trim($types) === '') {
            return ['sku', 'ean', 'upc', 'custom'];
        }

        $requestedTypes = array_values(array_filter(array_map(
            fn (string $type) => trim($type),
            explode(',', $types)
        )));

        $allowedTypes = array_map(
            fn (IdentifierType $type) => $type->value,
            IdentifierType::cases()
        );

        $invalidTypes = array_diff($requestedTypes, $allowedTypes);

        if (!empty($invalidTypes)) {
            throw new InvalidArgumentException('Unsupported identifier lookup type.');
        }

        return $requestedTypes ?: ['sku', 'ean', 'upc', 'custom'];
    }
}
